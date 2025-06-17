
# 🐉 ForgeAction

**ForgeAction** é uma aplicação web desenvolvida em Laravel, projetada para gerenciamento de personagens de RPG. O sistema permite o cadastro, visualização e gestão de fichas de personagens, consumindo uma API externa desenvolvida em Java com Spring Boot.

---

## 🚀 **Tecnologias utilizadas**
- **Backend / Frontend:** Laravel 11
- **API Externa:** Java Spring Boot (API RESTful)
- **Banco de Dados:** MySQL / PostgreSQL (dependendo do ambiente)
- **Frontend:** Blade + Bootstrap 5 + Select2
- **Containerização:** Docker + Railway (para deploy)
- **Versionamento:** GitHub

---

## 📁 **Estrutura de Pastas**
```
app/
 ├── Http/Controllers/    # Controladores das rotas
 ├── Models/              # Modelos do Laravel
 ├── Services/            # Serviços para consumir a API externa
bootstrap/
config/
database/
public/
resources/
 ├── css/                 # CSS customizado
 ├── js/                  # JS customizado
 └── views/               # Templates Blade
routes/
 ├── web.php              # Rotas web
storage/
```

---

## ⚙️ **Funcionalidades**
✅ Cadastro de usuário no ForgeAction e na API externa  
✅ Cadastro de personagem e distribuição de atributos (23 pontos, 8 atributos)  
✅ Tela de login e dashboard  
✅ Estrutura preparada para consumo seguro da API (via Http Client)  
✅ Interface responsiva (Bootstrap + Blade)  
✅ Deploy em Railway (com Docker)

---

## 📌 **Como rodar o projeto**
### Com Docker
```bash
docker build -t forgeaction .
docker run -p 8080:8080 forgeaction
```
➡️ Acesse no navegador: [http://localhost:8080](http://localhost:8080)

### Local (sem Docker)
1️⃣ Configure o `.env` com o banco e outras variáveis  
2️⃣ Instale dependências:
```bash
composer install
```
3️⃣ Gera a chave:
```bash
php artisan key:generate
```
4️⃣ Rode as migrations:
```bash
php artisan migrate
```
5️⃣ Suba o servidor:
```bash
php artisan serve
```

---

## 🌐 **Endpoints principais da API consumida**
- `POST /chave_personagem` → Cria chave do personagem (login + senha)  
- `GET /chave_personagem/check/{login}` → Recupera o chap_id  
- `POST /info_personagem` → Cadastra dados básicos do personagem  
- `POST /atributo_personagem` → Cadastra os atributos  
- `POST /bonus_personagem`, `POST /status_personagem` → Calcula e salva bônus e status  

---

## 📝 **Possíveis melhorias**
- Implementar autenticação completa via token no frontend
- Finalizar integração dinâmica das etapas do cadastro
- Melhorar testes automatizados
- Criar painel administrativo para gestão dos dados

---

## 🤝 **Contribuições**
Sinta-se à vontade para abrir PRs e issues no repositório!  

---

## 🔗 **Links úteis**
- [Repositório API Spring Boot](https://github.com/RokaiDeveloper/rpg-api)  
- [Repositório ForgeAction](https://github.com/TBAbestkid/ForgeAction)  
- [Deploy no Railway](https://forgeaction-production-6504.up.railway.app)

---

## 👑 **Autor**
`ForgeAction` foi desenvolvido por **Tba** e **Rokai** como projeto de estudo e apoio a sessões de RPG.
