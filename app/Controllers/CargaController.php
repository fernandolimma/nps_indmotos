<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\View;
use App\Models\ClienteOS;
use App\Models\Configuracao;
use App\Services\EvolutionApi;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class CargaController extends Controller
{
    private ClienteOS $clienteOS;
    private Configuracao $configuracao;

    public function __construct()
    {
        $this->requireAuth();
        $this->clienteOS = new ClienteOS();
        $this->configuracao = new Configuracao();
    }

    public function index(): void
    {
        $csrfToken = $this->generateCsrfToken();
        View::render('carga/index', [
            'csrf_token' => $csrfToken,
            'pendentes_email' => $this->clienteOS->countPendentes(),
            'pendentes_whatsapp' => !empty($this->configuracao->get()['evolution_enabled'])
                ? $this->clienteOS->countWhatsappPendentes()
                : 0,
            'registros_nao_enviados' => $this->clienteOS->getPendingImportedData()
        ]);
    }

    public function upload(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/carga');
        }

        if (!$this->validateCsrfToken($this->getPost('csrf_token'))) {
            Session::flash('error', 'Token inválido');
            $this->redirect('/carga');
        }

        if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Erro no upload do arquivo');
            $this->redirect('/carga');
        }

        $file = $_FILES['arquivo'];
        $allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            Session::flash('error', 'Formato de arquivo inválido. Use .xlsx');
            $this->redirect('/carga');
        }

        if (!class_exists(\ZipArchive::class)) {
            Session::flash('error', 'A extensao ZIP do PHP nao esta habilitada. Ative "extension=zip" no php.ini do XAMPP e reinicie o Apache.');
            $this->redirect('/carga');
        }

        try {
            $config = $this->configuracao->get();
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            $headers = array_shift($rows);
            $headerMap = [
                'Tipo O.S.' => 'tipo_os',
                'Nº O.S.' => 'numero_os',
                'Data Conclusão' => 'data_conclusao',
                'Modelo' => 'modelo_veiculo',
                'Pessoa' => 'nome_cliente',
                'E-mail Pessoa' => 'email_cliente',
                'Telefone Celular' => 'telefone_whatsapp'
            ];

            $clientesParaImportar = [];
            
            foreach ($rows as $row) {
                if (empty($row[array_search('Nº O.S.', $headers)])) continue;

                $data = [];
                foreach ($headers as $index => $header) {
                    $header = trim((string) $header);
                    if (isset($headerMap[$header])) {
                        $data[$headerMap[$header]] = $row[$index] ?? null;
                    }
                }

                if (empty($data['numero_os']) || empty($data['email_cliente'])) continue;

                $telefoneWhatsapp = trim((string) ($data['telefone_whatsapp'] ?? ''));
                if ($telefoneWhatsapp !== '') {
                    $telefoneWhatsapp = \App\Services\BrazilianPhoneNumber::normalize($telefoneWhatsapp);
                }
                
                if ($data['data_conclusao']) {
                    $data['data_conclusao'] = \DateTime::createFromFormat('d/m/Y', $data['data_conclusao'])?->format('Y-m-d');
                }

                $clientesParaImportar[] = [
                    'nome_cliente' => $data['nome_cliente'],
                    'email_cliente' => $data['email_cliente'],
                    'telefone_whatsapp' => $telefoneWhatsapp !== '' ? $telefoneWhatsapp : null,
                    'modelo_veiculo' => $data['modelo_veiculo'],
                    'tipo_os' => $data['tipo_os'],
                    'numero_os' => $data['numero_os'],
                    'data_conclusao' => $data['data_conclusao'],
                    'status_whatsapp' => $telefoneWhatsapp !== '' ? 'nao_enviado' : 'nao_disponivel'
                ];
            }

            foreach ($clientesParaImportar as $cliente) {
                $token = bin2hex(random_bytes(16));
                $dataLimite = date('Y-m-d', strtotime('+' . ($config['dias_validade'] ?? 7) . ' days'));
                $this->clienteOS->create([
                    'nome_cliente' => $cliente['nome_cliente'],
                    'email_cliente' => $cliente['email_cliente'],
                    'telefone_whatsapp' => $cliente['telefone_whatsapp'],
                    'modelo_veiculo' => $cliente['modelo_veiculo'],
                    'tipo_os' => $cliente['tipo_os'],
                    'numero_os' => $cliente['numero_os'],
                    'data_conclusao' => $cliente['data_conclusao'],
                    'token_unico' => $token,
                    'data_limite' => $dataLimite,
                    'status_whatsapp' => $cliente['status_whatsapp']
                ]);
            }

            Session::flash('success', count($clientesParaImportar) . ' clientes importados com sucesso!');
        } catch (\Throwable $e) {
            Session::flash('error', 'Erro ao processar arquivo: ' . $e->getMessage());
        }

        $this->redirect('/carga');
    }

    public function disparar(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/carga');
        }

        if (!$this->validateCsrfToken($this->getPost('csrf_token'))) {
            Session::flash('error', 'Token inválido');
            $this->redirect('/carga');
        }

        $config = $this->configuracao->get();
        $clientes = $this->clienteOS->getPendingNotifications();
        $enviadosEmail = 0;
        $falhasEmail = 0;
        $enviadosWhatsapp = 0;
        $falhasWhatsapp = 0;
        $evolution = null;
        $erros = [];
        $detalheErroWhatsapp = null;

        if (!empty($config['evolution_enabled'])) {
            try {
                $evolution = new EvolutionApi(
                    (string) ($config['evolution_url'] ?? ''),
                    (string) ($config['evolution_api_key'] ?? ''),
                    (string) ($config['evolution_instance'] ?? '')
                );
            } catch (\Throwable $e) {
                error_log('Configuração inválida da Evolution API: ' . $e->getMessage());
                $erros[] = 'WhatsApp não enviado: confira as configurações da Evolution API';
            }
        }

        foreach ($clientes as $cliente) {
            if ($cliente['status_email'] !== 'enviado') {
                try {
                    $mail = new PHPMailer(true);
                    
                    $mail->isSMTP();
                    $mail->Host = $config['smtp_host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $config['smtp_usuario'];
                    $mail->Password = $config['smtp_senha'];
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = $config['smtp_port'];
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom($config['email_remetente'], $config['nome_remetente']);
                    $mail->addAddress($cliente['email_cliente'], $cliente['nome_cliente']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Pesquisa de Satisfação - ' . View::appName();
                    
                    $linkPesquisa = \App\Core\Router::baseUrl('/pesquisa?token=' . $cliente['token_unico']);
                    $logoUrl = !empty($config['logo_path'])
                        ? \App\Core\Router::baseUrl('/' . ltrim(str_replace('\\', '/', $config['logo_path']), '/'))
                        : null;
                    
                    $mail->Body = View::render('emails/pesquisa', [
                        'cliente' => $cliente,
                        'link' => $linkPesquisa,
                        'logo_url' => $logoUrl
                    ], true);

                    $mail->send();
                    $this->clienteOS->updateStatusEmail($cliente['id'], 'enviado');
                    $enviadosEmail++;
                } catch (Exception $e) {
                    error_log('Erro ao enviar email: ' . $e->getMessage());
                    $this->clienteOS->updateStatusEmail($cliente['id'], 'retornado_erro');
                    $falhasEmail++;
                }
            }

            if (
                $evolution !== null
                && !empty($cliente['telefone_whatsapp'])
                && $cliente['status_whatsapp'] !== 'enviado'
            ) {
                try {
                    $linkPesquisa = \App\Core\Router::baseUrl('/pesquisa?token=' . $cliente['token_unico']);
                    $mensagem = sprintf(
                        "Olá, %s! Gostaríamos de ouvir sua opinião sobre o atendimento da %s referente à OS %s. Responda à pesquisa até %s: %s",
                        $cliente['nome_cliente'],
                        View::appName(),
                        $cliente['numero_os'],
                        date('d/m/Y', strtotime($cliente['data_limite'])),
                        $linkPesquisa
                    );

                    $evolution->sendText((string) $cliente['telefone_whatsapp'], $mensagem);
                    $this->clienteOS->updateStatusWhatsapp($cliente['id'], 'enviado');
                    $enviadosWhatsapp++;
                } catch (\Throwable $e) {
                    error_log('Erro ao enviar WhatsApp pela Evolution API para o registro ' . $cliente['id'] . ': ' . $e->getMessage());
                    $detalheErroWhatsapp ??= $e->getMessage();
                    $this->clienteOS->updateStatusWhatsapp($cliente['id'], 'retornado_erro');
                    $falhasWhatsapp++;
                }
            }
        }

        $resumo = "$enviadosEmail e-mails enviados";
        if ($falhasEmail > 0) {
            $erros[] = "$falhasEmail e-mails com erro";
        }
        if (!empty($config['evolution_enabled'])) {
            $resumo .= " e $enviadosWhatsapp WhatsApps enviados";
            if ($falhasWhatsapp > 0) {
                $erros[] = "$falhasWhatsapp WhatsApps com erro"
                    . ($detalheErroWhatsapp !== null ? ' (primeiro erro: ' . $detalheErroWhatsapp . ')' : '');
            }
        }

        if ($erros !== []) {
            Session::flash('error', $resumo . '. ' . implode('; ', $erros) . '.');
        } else {
            Session::flash('success', $resumo . '.');
        }

        $this->redirect('/carga');
    }

    public function limparPendentes(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/carga');
        }

        if (!$this->validateCsrfToken($this->getPost('csrf_token'))) {
            Session::flash('error', 'Token inválido');
            $this->redirect('/carga');
        }

        $removidos = $this->clienteOS->clearPendingImportedData();

        if ($removidos > 0) {
            Session::flash('success', "$removidos registros pendentes foram removidos com sucesso.");
        } else {
            Session::flash('error', 'Nenhum registro pendente foi encontrado para limpeza.');
        }

        $this->redirect('/carga');
    }
}
