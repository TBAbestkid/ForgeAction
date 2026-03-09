
# 🐉 ForgeAction

> **Uma plataforma web completa para gerenciamento de personagens de RPG, desenvolvida em Laravel 11 com integração a uma API externa em Java Spring Boot.**

ForgeAction é uma aplicação full-stack que permite aos usuários criar, gerenciar e visualizar fichas de personagens de RPG. O sistema oferece autenticação segura, integração com Google, gerenciamento de atributos distribuídos e cálculo automático de bônus e status do personagem.

---

## 🚀 **Tecnologias Utilizadas**

### Backend
- **Framework:** Laravel 11 (PHP 8.2+)
- **Frontend:** Blade Templates + TailwindCSS + Bootstrap 5
- **Build Tool:** Vite
- **API Externa:** Java Spring Boot (REST API)
- **Autenticação:** Laravel Auth + Google OAuth 2.0 (Laravel Socialite)

### Banco de Dados & Infraestrutura
- **Gerenciador de Sessões:** Database Session Driver
- **Cache:** Database Cache Driver
- **Fila:** Database Queue Driver
- **Broadcasting:** Log Channel (pode ser expandido para Redis)
- **Containerização:** Docker + Railway/Koyeb (deploy)

### Dependências Principais
- **guzzlehttp/guzzle** (7.9) - Cliente HTTP
- **laravel/socialite** (5.23) - Autenticação OAuth
- **laravel-notification-channels/webpush** (10.2) - Notificações Web Push
- **ladumor/laravel-pwa** (0.0.5) - PWA (Progressive Web App)
- **sendinblue/api-v3-sdk** (8.4) - Email Marketing
- **symfony/mailchimp-mailer** (7.3) - Integração Mailchimp
- **symfony/http-client** (7.3) - Cliente HTTP avançado

---

## 📂 **Estrutura do Projeto**

```
ForgeAction/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # Autenticação, reset de senha
│   │   │   ├── LoginController.php         # Login e logout
│   │   │   ├── RegisterController.php      # Registro de usuários
│   │   │   ├── DashboardController.php     # Home e dashboard
│   │   │   ├── UserController.php          # Gerenciamento de usuário
│   │   │   ├── PersonagemController.php    # CRUD de personagens
│   │   │   ├── SalaController.php          # Gerenciamento de salas
│   │   │   ├── SalaApiController.php       # Integração da sala com API
│   │   │   ├── BonusPersonagemController.php # Cálculo de bônus
│   │   │   ├── EnumController.php          # Enums e consultas de dados
│   │   │   ├── GoogleController.php        # Login com Google
│   │   │   └── ChatController.php          # Sistema de chat
│   │   ├── Kernel.php                      # Middleware global
│   │   └── Middleware/
│   │
│   ├── Models/
│   │   └── User.php                        # Modelo de Usuário
│   │
│   ├── Services/
│   │   └── ApiService.php                  # Cliente para API externa
│   │
│   ├── Mail/
│   │   ├── InviteMail.php                  # Email de convite
│   │   ├── ResetMail.php                   # Email de reset de senha
│   │   └── TestMail.php                    # Email de teste
│   │
│   ├── Helpers/
│   │   └── ApiResponse.php                 # Helper para respostas padronizadas
│   │
│   └── Providers/
│       └── AppServiceProvider.php          # Registra bindings e serviços
│
├── bootstrap/
│   ├── app.php                             # Bootstrap da aplicação
│   ├── providers.php                       # Carregamento de provedores
│   └── cache/                              # Cache de configurações
│
├── config/
│   ├── app.php                             # Configuração geral
│   ├── auth.php                            # Autenticação
│   ├── cache.php                           # Cache
│   ├── database.php                        # Banco de dados
│   ├── filesystems.php                     # Sistema de arquivos
│   ├── laravelpwa.php                      # PWA
│   ├── logging.php                         # Logging
│   ├── mail.php                            # Email
│   ├── queue.php                           # Filas
│   ├── services.php                        # Serviços (ex: API externa)
│   └── session.php                         # Sessões
│
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2025_06_12_121044_create_chave_personagem_table.php
│   │   ├── 2025_06_12_121049_create_info_personagem_table.php
│   │   ├── 2025_06_12_121056_create_atributo_personagem_table.php
│   │   ├── 2025_06_12_121103_create_bonus_personagem_table.php
│   │   └── 2025_06_12_121109_create_status_personagem_table.php
│   ├── factories/
│   │   └── UserFactory.php                 # Factory para usuários de teste
│   └── seeders/
│       └── DatabaseSeeder.php              # Seed de dados iniciais
│
├── public/
│   ├── index.php                           # Entry point
│   ├── robots.txt
│   ├── sitemap.xml
│   ├── service-worker.js                   # Service Worker (PWA)
│   ├── assets/
│   │   ├── site.webmanifest                # Manifest PWA
│   │   ├── ammo/                           # Recursos de munição/armas
│   │   ├── images/                         # Imagens da aplicação
│   │   └── themes/                         # Temas visuais
│   ├── css/
│   │   ├── app.css                         # CSS principal (Vite)
│   │   ├── forge-theme.css                 # Tema do Forge
│   │   ├── room.css                        # Estilo de salas
│   │   ├── room-status.css                 # Estilo de status de salas
│   │   └── style.css                       # CSS adicional
│   └── js/
│       ├── app.js                          # JS principal (Vite)
│       └── ...                             # Outros scripts
│
├── resources/
│   ├── css/
│   │   ├── app.css
│   │   └── ...
│   ├── js/
│   │   ├── app.js
│   │   └── ...
│   └── views/
│       ├── index.blade.php                 # Home pública
│       ├── login.blade.php                 # Formulário de login
│       ├── register.blade.php              # Formulário de registro
│       ├── dashboard.blade.php             # Dashboard logado
│       ├── profile.blade.php               # Perfil do usuário
│       ├── about.blade.php                 # Sobre ForgeAction
│       ├── registerPerson.blade.php        # Criação de personagem
│       ├── loading.blade.php               # Tela de carregamento
│       ├── chat.blade.php                  # Chat de teste
│       ├── dice.blade.php                  # Teste de dados
│       ├── personagem/
│       │   ├── index.blade.php             # Lista de personagens
│       │   ├── create.blade.php            # Criar personagem
│       │   ├── edit.blade.php              # Editar personagem
│       │   └── show.blade.php              # Visualizar personagem
│       ├── room/
│       │   └── ...                         # Views de salas
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       ├── emails/                         # Templates de email
│       └── partials/                       # Components reutilizáveis
│
├── routes/
│   ├── web.php                             # Rotas web principais
│   ├── api.php                             # Rotas de API (reservadas)
│   └── console.php                         # Comandos Artisan
│
├── storage/
│   ├── app/                                # Armazenamento de arquivos
│   ├── framework/                          # Cache do framework
│   └── logs/                               # Logs da aplicação
│
├── tests/
│   ├── TestCase.php
│   ├── Feature/                            # Testes de features
│   └── Unit/                               # Testes unitários
│
├── vendor/                                 # Dependências do Composer
│
├── .env.example                            # Exemplo de variáveis de ambiente
├── .env                                    # Variáveis reais (não versionado)
├── .gitignore                              # Arquivos ignorados pelo git
├── composer.json                           # Dependências do PHP
├── package.json                            # Dependências do Node.js
├── package-lock.json
├── artisan                                 # CLI do Laravel
├── phpunit.xml                             # Configuração de testes
├── vite.config.js                          # Configuração do Vite
├── tailwind.config.js                      # Configuração do TailwindCSS
├── postcss.config.js                       # Configuração do PostCSS
├── Dockerfile                              # Containerização
├── entrypoint.sh                           # Script de entrada Docker
├── deploy.yml                              # Pipeline de deploy
└── README.md                               # Este arquivo
```

---

## ⚙️ **Funcionalidades Principais**

### Autenticação & Usuários
- ✅ Registro e login com email/senha
- ✅ Autenticação com Google (OAuth 2.0)
- ✅ Recuperação de senha por email
- ✅ Gerenciamento de perfil (atualizar email, senha, role)
- ✅ Suporte a papéis (MASTER/PLAYER)
- ✅ Sessões seguras com database driver

### Gerenciamento de Personagens
- ✅ Criar novos personagens
- ✅ Distribuição automática de **23 pontos** entre **8 atributos**
- ✅ Atributos: Força, Agilidade, Inteligência, Sabedoria, Destreza, Vitalidade, Percepção, Carisma
- ✅ Cálculo automático de bônus (vida, mana)
- ✅ Cálculo automático de status (iniciativa, dano, ataque)
- ✅ Edição e visualização de fichas
- ✅ Seleção/deselecção de personagem ativo
- ✅ Suporte a múltiplos personagens por usuário

### Salas de RPG
- ✅ Criação de salas de jogo
- ✅ Gerenciamento de salas
- ✅ Status de salas em tempo real

### Recursos Adicionais
- ✅ Sistema de chat para salas
- ✅ Roller de dados para testes
- ✅ Interface responsiva (mobile-first)
- ✅ PWA (Progressive Web App) - funciona offline
- ✅ Web Push Notifications
- ✅ Service Worker

---

## 🔧 **Instalação & Configuração**

### Pré-requisitos
- **PHP 8.2+**
- **Composer** (gerenciador de dependências PHP)
- **Node.js 16+** e **npm** (para assets front-end)
- **Git**
- **Docker** (opcional, para containerização)

### Passo 1: Clonar o repositório
```bash
git clone https://github.com/TBAbestkid/ForgeAction.git
cd ForgeAction
```

### Passo 2: Instalar dependências PHP
```bash
composer install
```

### Passo 3: Instalar dependências Node.js
```bash
npm install
```

### Passo 4: Copiar arquivo de ambiente
```bash
cp .env.example .env
```

### Passo 5: Gerar chave da aplicação
```bash
php artisan key:generate
```

### Passo 6: Criar banco de dados (SQLite padrão)
O SQLite é criado automaticamente em `database/database.sqlite` na primeira migração.

Ou configure um MySQL/PostgreSQL no `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forgeaction
DB_USERNAME=root
DB_PASSWORD=
```

### Passo 7: Executar migrations
```bash
php artisan migrate
```

### Passo 8: Compilar assets
```bash
npm run build       # Para produção
npm run dev         # Para desenvolvimento (com watch)
```

### Passo 9: Servir a aplicação
```bash
php artisan serve
```

Acesse em: [http://localhost:8000](http://localhost:8000)

---

## 🌍 **Configuração de Variáveis de Ambiente**

Edite o arquivo `.env` com suas configurações:

```env
## Aplicação
APP_NAME=ForgeAction
APP_ENV=local              # local, testing, production
APP_KEY=                   # Gerado pelo php artisan key:generate
APP_DEBUG=true             # false em produção
APP_URL=http://localhost

## Banco de Dados
DB_CONNECTION=sqlite       # sqlite, mysql, pgsql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=forgeaction
# DB_USERNAME=root
# DB_PASSWORD=

## Mail (Sendinblue, Mailchimp)
MAIL_DRIVER=sendinblue     # ou outro driver
SENDINBLUE_API_KEY=        # Sua chave API

## Google OAuth
GOOGLE_CLIENT_ID=          # Do Google Cloud Console
GOOGLE_CLIENT_SECRET=      # Do Google Cloud Console
GOOGLE_REDIRECT_URL=http://localhost/login/google/callback

## API Externa (Spring Boot)
SERVICES_API_BASE_URL=https://narrow-christan-rokaideveloper-806169ef.koyeb.app
SERVICES_API_USER=admin
SERVICES_API_PASS=admin

## Sessão
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

## Cache e Fila
CACHE_STORE=database
QUEUE_CONNECTION=database
```

---

## 🌐 **Rotas Principais**

### Autenticação
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/login` | Formulário de login |
| POST | `/login` | Processar login |
| POST | `/logout` | Logout |
| GET | `/register` | Formulário de registro |
| POST | `/register` | Processar registro |
| GET | `/login/google` | Iniciar login com Google |
| GET | `/login/forgot-password` | Formulário de recuperação |
| POST | `/login/forgot-password` | Enviar email de recuperação |
| GET | `/login/reset-password/{token}` | Formulário de reset |
| POST | `/login/reset-password` | Processar reset |

### Páginas Públicas
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/` | Redireciona para /home |
| GET | `/home` | Home pública |
| GET | `/sobre-forgeaction` | Página sobre |
| GET | `/loading` | Tela de carregamento |
| GET | `/dados-teste` | Teste de dados (dice) |

### Dashboard & Perfil (Requer Login)
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/dashboard` | Dashboard principal |
| GET | `/perfil` | Página de perfil |
| PUT | `/perfil/email` | Atualizar email |
| PUT | `/perfil/role` | Atualizar papelação (MASTER/PLAYER) |
| PUT | `/perfil/senha` | Atualizar senha |
| GET | `/usuario` | Listar todos os usuários |
| GET | `/usuario/{usuarioId}` | Detalhes de um usuário |

### Personagens
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/registro-personagem` | Formulário de criação |
| POST | `/personagem/selecionar` | Selecionar personagem ativo |
| POST | `/personagem/deselecionar` | Deselecionar personagem |

---

## 🔌 **Integração com API Externa**

ForgeAction consome uma **API RESTful em Java Spring Boot** para gerenciar personagens. A integração é feita através da classe `ApiService`.

### Endpoints da API Consumida

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/chave_personagem` | Criar chave do personagem (login + senha) |
| GET | `/chave_personagem/check/{login}` | Verificar disponibilidade e recuperar chap_id |
| POST | `/info_personagem` | Cadastrar informações básicas (nome, classe, raça, etc.) |
| POST | `/atributo_personagem` | Registrar 8 atributos do personagem |
| POST | `/bonus_personagem` | Calcular e salvar bônus (vida, mana) |
| POST | `/status_personagem` | Calcular e salvar status (iniciativa, dano, ataque) |
| GET | `/api/personagem/{id}` | Recuperar personagem por ID |
| GET | `/api/personagem/usuario/{userId}` | Listar personagens de um usuário |
| GET | `/api/enum/classe` | Listar classes disponíveis |
| GET | `/api/enum/raca` | Listar raças disponíveis |

**Base URL:** `https://narrow-christan-rokaideveloper-806169ef.koyeb.app`

**Autenticação:** Basic Auth (user: `admin`, password: `admin`)

---

## 💻 **Estrutura de Controllers**

### AuthController
Gerencia autenticação, recuperação de senha e perfil.

```php
// Métodos principais
- forgotpassword()           // Exibir formulário
- sendResetLinkEmail()       // Enviar email
- showResetForm()            // Exibir formulário de reset
- reset()                    // Processar reset
- profile()                  // Exibir perfil
```

### PersonagemController
Gerencia criação, edição e visualização de personagens.

```php
// Métodos principais
- personagem()               // Exibir formulário
- show($id)                  // Recuperar personagem
- store()                    // Criar personagem
- select()                   // Selecionar personagem ativo
- deselect()                 // Deselecionar
```

### DashboardController
Gerencia a home e dashboard.

```php
// Métodos principais
- index()                    // Home pública
- dash()                     // Dashboard logado
- about()                    // Página sobre
- dice()                     // Teste de dados
```

### UserController
Gerencia dados do usuário.

```php
// Métodos principais
- profile()                  // Perfil do usuário
- updateEmail()              // Atualizar email
- updatePassword()           // Atualizar senha
- updateRole()               // Atualizar papelação
- get()                      // Listar usuários
- getById($id)               // Detalhes do usuário
```

---

## 🧪 **Testando a Aplicação**

### Testes Unitários
```bash
php artisan test --unit
```

### Testes de Feature
```bash
php artisan test --testsuite=Feature
```

### Todos os testes
```bash
php artisan test
```

---

## 🐳 **Deploy com Docker**

### Executar localmente
```bash
docker build -t forgeaction:latest .
docker run -p 8080:8000 --env-file .env forgeaction:latest
```

Acesse em: [http://localhost:8080](http://localhost:8080)

### Deploy em Railway
1. Faça push para GitHub
2. Conecte seu repositório no [Railway](https://railway.app)
3. Configure variáveis de ambiente
4. Deploy automático a cada push

**URL de Produção:** [https://forgeaction-production-6504.up.railway.app](https://forgeaction-production-6504.up.railway.app)

---

## 📊 **Arquitetura & Padrões**

### Padrão de Serviço
A classe `ApiService` encapsula toda a comunicação com a API externa, facilitando testes e manutenção.

```php
$api = new ApiService();
$personagens = $api->get("api/personagem/usuario/{$userId}");
$response = $api->post("api/personagem", $data);
```

### Respostas Padronizadas
Helper `ApiResponse` padroniza respostas JSON:

```php
return ApiResponse::success($data, 'Mensagem de sucesso');
return ApiResponse::error('Erro', 400);
```

### Middleware de Autenticação
Rotas protegidas usam o middleware `auth` do Laravel para garantir que apenas usuários logados acessem.

---

## 🎨 **Assets & Build**

### Vite
O projeto usa **Vite** para compilação de assets:

- **TailwindCSS** para estilos utilitários
- **Bootstrap 5** para componentes
- **PostCSS** para pré-processamento

```bash
npm run dev       # Modo desenvolvimento (watch)
npm run build     # Produção (minificado)
```

---

## 📁 **Boas Práticas**

1. **Antes de fazer push:** Sempre rode `php artisan test` para garantir testes passando
2. **Variáveis de ambiente:** Nunca commitar `.env`, use `.env.example`
3. **Segurança:** Configure `APP_DEBUG=false` em produção
4. **Migrations:** Sempre criar migrations para mudanças no banco
5. **Logs:** Verificar `storage/logs/` para debug de erros

---

## 🚀 **Próximos Passos & Melhorias**

- 🔄 Refatorar autenticação com tokens JWT
- 📱 Melhorar responsividade mobile
- 🧪 Aumentar cobertura de testes automatizados
- 👥 Painel administrativo para gestão centralizada
- ⚡ Implementar cache com Redis
- 📡 WebSockets para chat em tempo real (via Laravel Echo)
- 🌙 Modo escuro (dark mode)
- 🗣️ Suporte a múltiplos idiomas (i18n)

---

## 📞 **Suporte & Contribuições**

### Reportar Bugs
Abra uma [issue](https://github.com/TBAbestkid/ForgeAction/issues) no repositório.

### Contribuir
1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

---

## 📚 **Documentação & Links úteis**

- [Laravel 11 Documentation](https://laravel.com/docs/11)
- [Laravel Socialite (OAuth)](https://laravel.com/docs/11/socialite)
- [Vite Documentation](https://vitejs.dev/)
- [TailwindCSS](https://tailwindcss.com/)
- [Bootstrap 5](https://getbootstrap.com/)
- [Railway Deploy](https://railway.app/docs)
- [Repositório API Spring Boot](https://github.com/RokaiDeveloper/rpg-api)
- [Repositório ForgeAction](https://github.com/TBAbestkid/ForgeAction)  
- [Deploy em Produção](https://forgeaction-production-6504.up.railway.app)

---

## 📝 **Licença**
MIT License - Veja o arquivo [LICENSE](./LICENSE) para mais detalhes.

---

## 👥 **Autores**

**ForgeAction** foi desenvolvido colaborativamente como projeto de estudo e ferramental para sessões de RPG:

- **Tba** - Desenvolvimento frontend e integração
- **Rokai** - Desenvolvimento da API backend (Spring Boot)

Desenvolvido com ❤️ em 2025

---

## 📌 **Changelog**

### v1.0.0 (Atual)
- ✅ Sistema de autenticação completo
- ✅ Gerenciamento de personagens
- ✅ Integração com API Spring Boot
- ✅ Suporte a PWA
- ✅ Deploy automatizado
- ✅ Login com Google OAuth

---

**Status do Projeto:** 🟢 Em Desenvolvimento Ativo
