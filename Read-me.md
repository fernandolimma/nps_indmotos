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
- Quando a Evolution API estiver habilitada e configurada, o mesmo disparo também envia uma mensagem de texto pelo WhatsApp com o link da pesquisa para os registros com telefone. O status de envio é controlado separadamente por canal; uma falha em um canal não impede a tentativa no outro, e os canais com erro podem ser tentados novamente.
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
- Conexão do WhatsApp pela Evolution API: crie a instância na primeira conexão e use **Conectar ou gerar novo QR code** para refazer o pareamento. Ao confirmar a conexão, o envio por WhatsApp é ativado automaticamente; URL e API key são obtidas da configuração do servidor.
- A integração usa a Evolution API: `GET /instance/connectionState/{instanceName}` para testar a conexão e `POST /message/sendText/{instanceName}` para enviar a mensagem de texto, autenticando pelo cabeçalho `apikey`. Consulte o [repositório oficial](https://github.com/evolution-foundation/evolution-api).
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

- PHP 8.2 ou superior (a imagem Docker usa PHP 8.3, compatível com as dependências travadas no `composer.lock`).
- MySQL com suporte a InnoDB.
- Composer.
- `phpoffice/phpspreadsheet` para leitura de planilhas.
- `phpmailer/phpmailer` para envio SMTP.
- Evolution API para envio opcional de mensagens WhatsApp (requer a extensão cURL do PHP).
- Extensão ZIP do PHP habilitada para processar arquivos `.xlsx`.
- Interface baseada em Bootstrap 5, carregado via CDN.

## Instalação com Docker (recomendada)

Requisitos: Docker Engine e Docker Compose v2 (no Windows, Docker Desktop).

1. Na raiz do projeto, execute no PowerShell:

   ```powershell
   .\docker\setup.ps1
   ```

   O script cria ou completa `.env` com credenciais aleatórias (mantenha esse arquivo privado), constrói a imagem PHP/Apache e inicia a aplicação, MySQL, Evolution API, PostgreSQL e Redis. A aplicação aguarda o banco ficar pronto e cria as tabelas automaticamente na primeira inicialização.

2. Abra `http://localhost:8081/setup` e crie a conta MASTER. A aplicação fica disponível em `http://localhost:8081`; a porta pode ser alterada em `APP_PORT` no `.env`.

3. Configure dados SMTP válidos em **Configurações**. Em uma instalação nova, o banco Docker é inicializado com perguntas e configurações de exemplo; crie o primeiro usuário em `/setup`. Nesta implantação, o banco e os uploads existentes no XAMPP foram migrados; em instalações futuras, essa cópia **não é automática**.

4. Para conectar o WhatsApp, entre como MASTER e abra **Configurações**. Na primeira conexão, crie a instância; nas próximas, use **Conectar ou gerar novo QR code**. Escaneie-o em **WhatsApp > Dispositivos conectados > Conectar dispositivo** e confirme a conexão para ativar os envios. Na primeira inicialização, a Evolution pode demorar para aplicar migrações; acompanhe com `docker compose logs -f evolution-api`.

5. Na carga das OS, inclua a coluna `Telefone Celular` com DDD brasileiro. A aplicação acrescenta o código `55` quando necessário.

Uploads e os bancos da aplicação/Evolution ficam em volumes Docker nomeados. `docker compose down` para os containers sem apagar os dados; **não use `docker compose down -v`**, pois `-v` remove esses volumes. Faça backup dos volumes antes de atualizar ou mover o servidor.

Para VPS, configure o proxy reverso HTTPS para encaminhar ao endereço local da aplicação. Defina `APP_BASE_URL=https://seu-dominio` no `.env` para que os links públicos de pesquisa usem HTTPS atrás do proxy. Por padrão, a porta da aplicação e a porta de diagnóstico da Evolution ficam vinculadas a `127.0.0.1`; o MySQL, PostgreSQL e Redis não são publicados no host. Nunca exponha as portas internas dos bancos ou a API Evolution diretamente à Internet.

O Docker cria um banco novo. Antes de migrar uma instalação XAMPP existente, faça e valide um backup do MySQL; não remova nem altere o banco antigo até confirmar os dados no novo ambiente.

### VPS com Dokploy/Traefik

O arquivo `docker-compose.vps.yml` publica a aplicação na rede `dokploy-network`, configura o domínio `nps.vorticehub.com` com TLS pelo Traefik e desloca a porta local da Evolution para evitar conflito com outros serviços da VPS. O registro DNS `A` do subdomínio deve apontar para o IP da VPS.

Configure as quatro senhas do `.env` com valores aleatórios fortes e defina `APP_BASE_URL=https://nps.vorticehub.com`. Suba a aplicação e seus serviços com:

```sh
docker compose -f docker-compose.yml -f docker-compose.vps.yml up -d --build
```

Para atualizar a implantação clonada do GitHub, use `git pull --ff-only` e execute novamente o mesmo comando. A Deploy key da VPS deve ter acesso somente de leitura ao repositório; mantenha a chave privada no servidor e não a inclua no `.env` ou no Git.

## Instalação local (XAMPP)

O modo XAMPP continua disponível para desenvolvimento:

1. Instale as dependências com `composer install`.
2. Crie o banco importando [`database/schema.sql`](database/schema.sql). Para bancos existentes, aplique [`database/migrations/20261023_evolution_whatsapp.sql`](database/migrations/20261023_evolution_whatsapp.sql) se as colunas ainda não existirem.
3. A conexão pode ser configurada por `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASSWORD`. Sem essas variáveis, mantém os padrões locais: `localhost`, `nps_indmotos`, `root` e senha vazia.
4. Ative as extensões ZIP e cURL no PHP do XAMPP e reinicie o Apache.
5. Acesse o endereço local configurado no Apache (por exemplo, `http://nps-indmotos.local/setup`) para criar a conta MASTER.
6. Configure SMTP antes de disparar pesquisas. Para WhatsApp, instale os serviços Docker usando o procedimento acima; no XAMPP, a API local é acessada em `http://127.0.0.1:8080`.

O ponto de entrada HTTP é `public/index.php`; o Docker configura o Apache para usar `public/` como raiz do site.

## Estrutura do projeto

```text
app/
  Controllers/  Controladores de autenticação, carga, dashboard, perguntas,
                pesquisa, relatórios e configurações
  Core/         Roteador, sessão, acesso ao banco, controlador base e views
  Models/       Acesso aos dados de usuários, configurações, OS,
                perguntas e respostas
  Views/        Telas administrativas, pesquisa pública e modelo de e-mail
Dockerfile      Imagem PHP/Apache da aplicação
docker-compose.yml  Aplicação, MySQL e serviços da Evolution API
docker/         Configuração PHP e script de instalação no Windows
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
