# NPS Indmotos

Aplicação web em PHP para importar ordens de serviço (OS), enviar pesquisas de satisfação por e-mail e acompanhar as respostas dos clientes.

## Funcionalidades

### Acesso e usuários

- Configuração inicial para criar o primeiro usuário MASTER; depois que existe ao menos um usuário, a rota de configuração inicial redireciona para o login.
- Login por e-mail e senha, com senha armazenada usando hash; encerramento de sessão pela opção **Sair**.
- Perfis `MASTER` e `USUARIO`. Ambos acessam dashboard, carga, perguntas e relatórios. Apenas o MASTER acessa configurações e gerenciamento de usuários.
- O MASTER pode criar usuários dos dois tipos e removê-los.

### Importação e envio de pesquisas

- Importação de planilhas Excel `.xlsx` com dados de OS. O arquivo deve conter as colunas:
  - `Tipo O.S.`
  - `Nº O.S.`
  - `Data Conclusão`
  - `Modelo`
  - `Pessoa`
  - `E-mail Pessoa`
- Opcionalmente, a coluna `Telefone Celular` com DDD para receber a pesquisa por WhatsApp. Na importação, o sistema remove caracteres de formatação e acrescenta o prefixo brasileiro `55` quando ele não foi informado; números fora do padrão brasileiro são rejeitados.
- Cada registro importado recebe um token exclusivo e uma data limite para resposta, calculada com o prazo configurado.
- A tela **Carga** apresenta os registros que ainda aguardam envio e permite disparar as pesquisas por e-mail e, quando habilitado, por WhatsApp.
- Quando a Evolution API estiver habilitada e configurada, o mesmo disparo também envia uma mensagem WhatsApp para os registros com telefone. O status de envio é controlado separadamente por canal; uma falha em um canal não impede a tentativa no outro, e os canais com erro podem ser tentados novamente.
- Os e-mails são enviados individualmente, com nome do cliente, número da OS, prazo e link exclusivo para a pesquisa. A logo configurada também pode aparecer no e-mail.
- O sistema acompanha separadamente os estados de e-mail (`nao_enviado`, `enviado` ou `retornado_erro`) e WhatsApp (`nao_enviado`, `enviado`, `retornado_erro` ou `nao_disponivel`). É possível limpar registros pendentes que ainda não tiveram envio em nenhum canal.

### Pesquisa pública

- A pesquisa é acessada por link com token individual, sem necessidade de login.
- Antes de exibir o formulário, o sistema verifica se o token existe, se a pesquisa ainda não foi respondida e se o prazo não expirou.
- O formulário mostra os dados da OS e as perguntas ativas, ordenadas pela ordem definida.
- Há suporte a perguntas de escala de 0 a 10 e perguntas discursivas. A resposta da escala é obrigatória; a discursiva é opcional.
- O envio grava as respostas, marca a pesquisa como respondida e apresenta uma confirmação. Tokens inválidos, pesquisas já respondidas ou expiradas são apresentados como erro.

### Perguntas

- Cadastro, edição, ativação/desativação, ordenação e remoção de perguntas.
- Tipos disponíveis: escala de 0 a 10 e resposta discursiva.
- Somente perguntas ativas aparecem na pesquisa pública.
- O banco é inicializado com uma pergunta de recomendação e outra sobre a experiência com o atendimento.

### Dashboard

Exibe indicadores do mês atual e do envio:

- total de pesquisas importadas no mês;
- pesquisas respondidas no mês;
- quantidade de e-mails ainda não enviados;
- média das notas e média das notas das pesquisas classificadas como detratoras.

**Observação sobre os indicadores:** apesar do rótulo “Média NPS”, o sistema calcula a média aritmética das notas de 0 a 10 e a multiplica por 10 para exibição. Isso não corresponde ao cálculo convencional do NPS (percentual de promotores menos percentual de detratores). A nota exibida nos relatórios também é a média das notas da pesquisa multiplicada por 10.

### Relatórios

- Lista de pesquisas respondidas, paginada em 20 registros por página. Por padrão, exibe as respostas do mês atual; aceita filtro por data inicial e final.
- Detalhe por registro com os dados do cliente/OS e as respostas dadas a cada pergunta.
- Listas paginadas de pesquisas ainda não respondidas e de e-mails com erro no envio, também com filtro de datas.

### Configurações (MASTER)

- Configuração de host, porta, usuário e senha SMTP, além do e-mail e nome do remetente.
- Prazo de validade da pesquisa em dias, usado para calcular a data limite dos próximos registros importados.
- URL base, nome da instância e API key da Evolution API, com opção de ativar o envio de pesquisas por WhatsApp junto com o e-mail. O botão **Testar conexão da Evolution API** consulta o estado da instância antes do disparo; a chave salva é mantida quando o campo de API key fica em branco.
- A integração usa a API oficial da Evolution: `GET /instance/connectionState/{instanceName}` para testar a conexão e `POST /message/sendText/{instanceName}` para enviar mensagens, autenticando pelo cabeçalho `apikey`. Consulte o [repositório oficial](https://github.com/evolution-foundation/evolution-api).
- Para a instalação local com Docker, execute `.\docker\setup.ps1` na raiz do projeto com o Docker Desktop aberto. O script gera segredos locais e inicia a Evolution API, PostgreSQL e Redis; a tela de configurações cria a instância, mostra o QR code e ativa o WhatsApp após verificar a conexão.
- Envio de logo para personalizar as telas de acesso e pesquisa e o e-mail. Aceita PNG, JPG, GIF ou WEBP até 2 MB.
- Gerenciamento de contas de usuário.

## Fluxo principal

1. O MASTER configura o SMTP, o remetente, a validade das pesquisas e, opcionalmente, a logo e a Evolution API.
2. Um usuário importa a planilha de OS pela tela **Carga**; para o WhatsApp, a planilha também deve conter `Telefone Celular`.
3. Após conferir os registros importados, dispara os e-mails pendentes e, se habilitado, as mensagens WhatsApp.
4. O cliente abre o link recebido e responde à pesquisa dentro do prazo.
5. A equipe acompanha respostas, pendências e erros de envio no dashboard e nos relatórios.

## Tecnologias e dependências

- PHP 8.2 ou superior.
- MySQL com suporte a InnoDB.
- Composer.
- `phpoffice/phpspreadsheet` para leitura de planilhas.
- `phpmailer/phpmailer` para envio SMTP.
- Evolution API para envio opcional de mensagens WhatsApp (requer a extensão cURL do PHP).
- Extensão ZIP do PHP habilitada para processar arquivos `.xlsx`.
- Interface baseada em Bootstrap 5, carregado via CDN.

## Instalação local (XAMPP)

1. Instale as dependências PHP:

   ```bash
   composer install
   ```

2. Crie o banco e as tabelas importando [`database/schema.sql`](database/schema.sql) no MySQL. O script cria o banco `nps_indmotos`, as tabelas e os registros iniciais de configuração e perguntas.
   Em instalações existentes, execute [`database/migrations/20261023_evolution_whatsapp.sql`](database/migrations/20261023_evolution_whatsapp.sql) no MySQL para adicionar as colunas da Evolution API e do telefone. O script verifica cada coluna e pode ser executado novamente sem duplicá-las. As mesmas colunas também são verificadas automaticamente quando os modelos do sistema são carregados.
3. Confira a conexão com o banco em [`app/Core/Database.php`](app/Core/Database.php). Atualmente, host, banco, usuário e senha são definidos diretamente nesse arquivo; os valores iniciais são `localhost`, `nps_indmotos`, `root` e senha vazia.
4. Ative a extensão ZIP no PHP do XAMPP (`extension=zip` no `php.ini`) e reinicie o Apache.
5. Se o Apache estiver configurado com o virtual host local do projeto, adicione `127.0.0.1 nps-indmotos.local` ao arquivo `C:\Windows\System32\drivers\etc\hosts`, reinicie o Apache e acesse `http://nps-indmotos.local/setup` para criar o usuário MASTER. Caso use outra configuração de host/document root, acesse o endereço correspondente à instalação.
6. Configure dados SMTP válidos em **Configurações** antes de disparar pesquisas. O registro inicial de configuração do banco contém valores de exemplo e não envia e-mails sem credenciais reais.
7. Para instalar a Evolution API localmente no Docker Desktop, execute no PowerShell, a partir da pasta do projeto:

   ```powershell
   .\docker\setup.ps1
   ```

   O script cria `.env` com chaves aleatórias (não publique esse arquivo), baixa e inicia a Evolution API, PostgreSQL e Redis. Na primeira execução, a API aplica as migrações do banco e pode levar alguns minutos para iniciar; o script aguarda até 10 minutos. A API fica disponível somente no próprio computador em `http://127.0.0.1:8080`. Quando o script confirmar que ela está respondendo, entre como MASTER em **Configurações**.
8. Na seção **Instalação local com Docker**, escolha um nome de instância e clique em **Criar instância e gerar QR code**. No WhatsApp do celular, abra **Dispositivos conectados > Conectar dispositivo** e leia o QR code exibido. Após conectar, clique em **Já conectei: verificar e ativar WhatsApp**.
9. Na importação das OS, inclua a coluna `Telefone Celular`; o número deve conter DDD brasileiro. A aplicação armazena o telefone em dígitos, com o código `55`; quando o WhatsApp estiver conectado e ativado, o botão de disparo também enviará as pesquisas por esse canal.

Para parar os serviços sem apagar os dados, execute `docker compose down` na pasta do projeto. Não use `docker compose down -v`, pois a opção `-v` apaga os volumes persistentes da Evolution API.

O ponto de entrada HTTP fica em `public/`; os arquivos `.htaccess` do projeto encaminham as requisições para essa pasta e para o roteador da aplicação.

## Estrutura do projeto

```text
app/
  Controllers/  Controladores de autenticação, carga, dashboard, perguntas,
                pesquisa, relatórios e configurações
  Core/         Roteador, sessão, acesso ao banco, controlador base e views
  Models/       Acesso aos dados de usuários, configurações, OS,
                perguntas e respostas
  Views/        Telas administrativas, pesquisa pública e modelo de e-mail
database/
  schema.sql    Criação do banco, tabelas e dados iniciais
public/
  index.php     Ponto de entrada e definição das rotas
  imagens/      Ícones da aplicação
  uploads/      Arquivos enviados, incluindo a logo
```

## Rotas principais

| Rota | Acesso | Finalidade |
| --- | --- | --- |
| `/setup` | Público, somente sem usuários cadastrados | Criar o primeiro MASTER |
| `/login` e `/logout` | Público / usuário autenticado | Entrar e sair |
| `/dashboard` | Autenticado | Indicadores do mês |
| `/carga` | Autenticado | Importar OS, disparar e-mails e limpar importações pendentes |
| `/perguntas` | Autenticado | Gerenciar perguntas da pesquisa |
| `/pesquisa?token=...` | Público com token válido | Responder à pesquisa |
| `/relatorios` | Autenticado | Consultar respostas e filtrar por datas |
| `/relatorios/pendentes` | Autenticado | Consultar pesquisas ainda não respondidas |
| `/relatorios/erros` | Autenticado | Consultar e-mails com falha |
| `/configuracoes` | MASTER | Configurar SMTP, Evolution API, validade, logo e usuários |

## Modelo de dados

- `usuarios`: contas, hash de senha e perfil.
- `configuracoes`: parâmetros SMTP e Evolution API, remetente, logo e validade.
- `perguntas`: texto, tipo, estado de ativação e ordem.
- `clientes_os`: dados importados de cliente/OS, telefone opcional, token, prazo e estados de resposta e envio de e-mail/WhatsApp.
- `respostas`: resposta por pergunta e cliente/OS; vinculada às tabelas correspondentes por chaves estrangeiras.
