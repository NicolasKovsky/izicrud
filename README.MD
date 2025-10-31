# LaraVue IzCRUD

Bem-vindo ao **LaraVue IzCRUD**! Este kit é baseado no Starter Kit oficial do Laravel 12 com Vue, mas foi aprimorado com a adição de um poderoso gerador de CRUDs, desenvolvido para acelerar ainda mais o desenvolvimento de aplicações web modernas.

Desenvolvido por **Nicolas Slujalkovsky**, este projeto reflete minha paixão por criar ferramentas que simplificam o dia a dia de desenvolvedores.

---

## ✨ Recursos Principais

- **CRUD Generator**: Crie modelos, controladores, views Vue, migrações, rotas e itens de menu com um único comando.
- **Stack Moderna**: Laravel 12 backend + Vue.js frontend.
- **Estrutura Pronta**: Arquivos organizados para iniciar seu projeto rapidamente.
- **Integração Sidebar**: Adiciona automaticamente itens de menu ao sidebar.
- **Customizável**: Adapte facilmente às suas necessidades.

---

## 🚀 Como Usar

### Pré-requisitos
- PHP 8.2+
- Composer
- Node.js & npm
- Laravel 12

### Instalação
1. **Clone o repositório**:
   ```bash
   git clone https://github.com/NicolasKovsky/izicrud.git
   cd starter-kit-laravel-vue
   ```
2. **Instale as dependências do PHP**:
   ```bash
   composer install
   ```
3. **Instale as dependências do frontend**:
   ```bash
   npm install
   ```
4. **Configure o arquivo `.env`**:
   - Copie `.env.example` para `.env` e ajuste as configurações (banco de dados, etc.).
5. **Gere a chave da aplicação**:
   ```bash
   php artisan key:generate
   ```
6. **Execute as migrações**:
   ```bash
   php artisan migrate
   ```
7. **Inicie o servidor**:
   ```bash
   php artisan serve
   npm run dev
   ```

---

## 🛠️ Gerando CRUDs

Use o comando `make:crud` para criar um CRUD completo em segundos:

```bash
php artisan make:crud Post title:"Título":string content:"Conteúdo":text
```

### Tipos de Campos Suportados

Os seguintes tipos de campos são suportados ao gerar um CRUD:

| **Tipo**       | **Descrição**                          |
|-----------------|----------------------------------------|
| `string`        | Texto curto (ex.: nome, título).       |
| `text`          | Texto longo (ex.: descrição).         |
| `integer`       | Número inteiro.                      |
| `bigInteger`    | Número inteiro grande.               |
| `float`         | Número de ponto flutuante.           |
| `double`        | Número de precisão dupla.            |
| `decimal`       | Número decimal exato.                |
| `boolean`       | Valor verdadeiro ou falso.           |
| `date`          | Data (YYYY-MM-DD).                   |
| `datetime`      | Data e hora (YYYY-MM-DD HH:MM:SS).   |
| `timestamp`     | Timestamp com data e hora.           |
| `json`          | Dados no formato JSON.               |
| `email`         | Endereço de e-mail.                  |

### O que será gerado?
- **Modelo**: `app/Models/Post.php`
- **Controlador**: `app/Http/Controllers/PostController.php`
- **Views Vue**: `resource/js/pages/posts/index.vue` e `create.vue`
- **Migração**: `database/migrations/[timestamp]_create_posts_table.php`
- **Rotas**: Adicionadas em `routes/web.php`
- **Menu**: Item adicionado em `resource/js/components/AppSidebar.vue`

---

## 🖥️ Interface de Desenvolvimento para CRUDs

Além do comando `make:crud`, você pode acessar uma interface gráfica para gerar CRUDs diretamente no navegador. Isso facilita o processo de configuração e criação.

### Como acessar?
1. Certifique-se de que o servidor Laravel está em execução:
   ```bash
   php artisan serve
   ```
2. Acesse a rota específica no navegador:
   ```
   http://localhost:8000/dev/crud-generator
   ```

### Recursos da interface
- **Formulário intuitivo**: Preencha os campos necessários para gerar o CRUD.
- **Pré-visualização**: Veja os arquivos que serão criados antes de confirmar.
- **Geração instantânea**: Clique em "Gerar" para criar o CRUD e visualizar os resultados.

---

## 📋 Exemplo de Saída

```
✅ CRUD para 'Post' gerado com sucesso!
------------------------------------------------
📄 Arquivos criados:
  • Modelo: app/Models/Post.php
  • Controlador: app/Http/Controllers/PostController.php
  • Views:
    - resource/js/pages/posts/index.vue
    - resource/js/pages/posts/create.vue
  • Migração: database/migrations/2025_03_23_123456_create_posts_table.php
  • Rotas adicionadas em: routes/web.php
  • Item de menu adicionado em: resource/js/components/AppSidebar.vue
------------------------------------------------
📋 Campos configurados:
  • 'Título' - title - string
  • 'Conteúdo' - content - text
------------------------------------------------
🎉 Pronto! Verifique os arquivos e ajuste conforme necessário.
Criado por: Nicolas Slujalkovsky
Conecte-se comigo: linkedin.com/in/nicolas-slujalkovsky
Starter Kit Laravel 12 + Vue - Feito para acelerar seu desenvolvimento!
```

---

## 🤝 Contribuições

Contribuições são bem-vindas! Sinta-se à vontade para abrir *issues* ou enviar *pull requests* no [repositório GitHub](https://github.com/NicolasKovsky/izicrud).

---

## 👤 Autor

**Nicolas Slujalkovsky**
Entusiasta de Laravel e Vue.js, apaixonado por criar soluções que tornam o desenvolvimento mais rápido e eficiente.
📬 **Conecte-se comigo**: [linkedin.com/in/nicolas-slujalkovsky](https://linkedin.com/in/nicolas-slujalkovsky)
🌐 **Veja mais projetos**: [github.com/nicolas-slujalkovsky](https://github.com/nicolas-slujalkovsky)

---

## 📜 Licença

Este projeto é licenciado sob a [MIT License](LICENSE).

