# API de Upload e Processamento de Arquivos

Esta API permite o upload e processamento de arquivos CSV e XLSX, com validação de duplicidade através de hash e processamento assíncrono dos dados.

## Requisitos

- PHP 8.1 ou superior
- Composer
- MySQL/PostgreSQL
- Laravel 10.x

## Instalação

1. Clone o repositório
```bash
git clone [url-do-repositorio]
```

2. Instale as dependências
```bash
composer install
```

3. Configure o arquivo .env
```bash
cp .env.example .env
```

4. Configure as variáveis de ambiente no .env:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

QUEUE_CONNECTION=database
```

5. Execute as migrations
```bash
php artisan migrate
```

6. Crie o link simbólico para storage
```bash
php artisan storage:link
```

7. Inicie o worker para processamento de filas
```bash
php artisan queue:work 
```

ou (para ambiente de desenvolvimento rodando o servidor do laravel o pail e o work numa unica chamada vantagem de poder acompanhar o log em tempo real) 

```bash
composer run dev
```

## Documentação da API (Swagger)

A API possui documentação completa usando Swagger/OpenAPI. Para acessar:

### Acesso à Documentação

1. **Ambiente Local (Desenvolvimento)**
   ```bash
   # Inicie o servidor Laravel
   php artisan serve
   
   # Acesse a documentação em:
   http://localhost:8000/api/documentation
   ```

2. **Ambiente Docker**
   ```bash
   # Inicie os containers
   docker-compose up -d
   
   # Acesse a documentação em:
   http://localhost/api/documentation
   ```

3. **Acesso Direto**
   - A raiz do projeto (`/`) redireciona automaticamente para a documentação
   - URLs alternativas:
     - `/api/documentation`

**Geração da Documentação**
  ```bash
   # Gera a documentação inicial
   php artisan l5-swagger:generate
   
   # Atualiza a documentação após alterações
   php artisan l5-swagger:generate
   ```


### Recursos da Documentação

- **Interface Interativa**: Teste os endpoints diretamente pelo navegador
- **Modelos de Dados**: Visualize a estrutura dos dados
- **Exemplos de Requisição/Resposta**: Veja exemplos de uso
- **Autenticação**: Documentação dos endpoints protegidos

### Endpoints Documentados

#### Uploads
- `GET /api/v1/uploads`: Lista todos os uploads
- `POST /api/v1/uploads`: Realiza upload de arquivo
- `DELETE /api/v1/uploads/{id}`: Remove um upload

#### Consolidação
- `GET /api/v1/consolidate`: Lista dados consolidados

### Modelos Documentados

#### Upload
```json
{
    "id": "integer",
    "file_path": "string",
    "name_file": "string",
    "date_upload": "date",
    "hash_file": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

#### ConsolidateFile
```json
{
    "id": "integer",
    "RptDt": "date",
    "TckrSymb": "string",
    "MktNm": "string",
    "SctyCtgyNm": "string",
    "ISIN": "string",
    "CrpnNm": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

### Atualizando a Documentação

Após fazer alterações nos controllers ou modelos, gere a documentação atualizada:
```bash
php artisan l5-swagger:generate
```

## Estrutura do Projeto

### Models

- `Upload`: Gerencia o upload de arquivos
  - Validação de duplicidade via hash SHA256
  - Armazenamento em disco local
  - Dispara job de processamento

- `ConsolidateFile`: Processa e armazena os dados dos arquivos
  - Campos: RptDt, TckrSymb, MktNm, SctyCtgyNm, ISIN, CrpnNm

### Jobs

- `ProcessDataConsolidateFile`: Processa arquivos de forma assíncrona
  - Suporte para CSV e XLSX
  - Utiliza OpenSpout para leitura eficiente
  - Timeout configurável (padrão: 10 minutos)

### Controllers

- `UploadController`: Endpoints para gerenciamento de uploads
  - POST /api/v1/uploads: Upload de arquivo
  - GET /api/v1/uploads: Lista uploads
  - DELETE /api/v1/uploads/{id}: Remove upload

- `ConsolidateFileController`: Endpoints para dados consolidados
  - GET /api/v1/consolidate: Lista dados consolidados

## Endpoints da API

### Upload de Arquivo
```http
POST /api/v1/uploads
Content-Type: multipart/form-data

file: [arquivo]
```

Resposta (200):
```json
{
    "message": "upload com sucesso"
}
```

### Listar Uploads
```http
GET /api/v1/uploads
```

Resposta (200):
```json
{
    "data": [
        {
            "id": 1,
            "file_path": "uploads/arquivo.csv",
            "name_file": "arquivo.csv",
            "date_upload": "2024-03-14",
            "hash_file": "hash123..."
        }
    ],
    "current_page": 1,
    "per_page": 50
}
```

### Listar Dados Consolidados
```http
GET /api/v1/consolidate
```

Resposta (200):
```json
{
    "data": [
        {
            "RptDt": "2024-03-14",
            "TckrSymb": "PETR4",
            "MktNm": "B3",
            "SctyCtgyNm": "Ações",
            "ISIN": "BRPETRACNPR6",
            "CrpnNm": "Petrobras"
        }
    ],
    "current_page": 1,
    "per_page": 50
}
```

## Processamento de Arquivos

1. O arquivo é recebido via API
2. É gerado um hash SHA256 para verificar duplicidade
3. O arquivo é salvo em storage/app/uploads
4. Um job é disparado para processamento assíncrono
5. O job lê o arquivo usando OpenSpout
6. Os dados são processados e salvos na tabela consolidate_files

## Validações

- Tipos de arquivo permitidos: CSV, XLS, XLSX
- Tamanho máximo: 150MB
- Verificação de duplicidade via hash
- Validação de estrutura do arquivo

## Logs

Os logs são armazenados em `storage/logs/laravel.log` e incluem:
- Processamento de arquivos
- Erros de upload
- Processamento de jobs
- Validações

## Segurança

- Validação de tipos de arquivo
- Verificação de duplicidade
- Processamento assíncrono
- Timeout configurável para jobs

## Manutenção

Para limpar jobs antigos:
```bash
php artisan queue:prune
```

Para reiniciar o worker:
```bash
php artisan queue:restart
```
