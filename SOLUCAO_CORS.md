# Solução: Problema de CORS no Login

## ✅ Correções Aplicadas

### 1. Arquivo `config/cors.php` Atualizado

**Alterações:**
- ✅ Adicionado `http://127.0.0.1:5173` aos origins permitidos
- ✅ Adicionado `Authorization` aos headers expostos
- ✅ Mantido `supports_credentials` como `true`

### 2. Arquivo `config/sanctum.php` Atualizado

**Alterações:**
- ✅ Adicionado `localhost:5173` e `127.0.0.1:5173` aos domínios stateful
- ✅ Configurado para ler `FRONTEND_URL` do `.env`

---

## 🔧 Configuração Necessária

### Passo 1: Atualizar o arquivo `.env`

Adicione estas linhas ao seu arquivo `.env` (crie se não existir):

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:8000,127.0.0.1:5173,127.0.0.1:8000
```

### Passo 2: Limpar o Cache

```bash
cd back/src
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Passo 3: Reiniciar o Servidor

```bash
# Se estiver usando Vagrant
vagrant halt
vagrant up

# Ou se estiver usando php artisan serve
# Pare o servidor (Ctrl+C) e inicie novamente
php artisan serve
```

---

## 🌐 Configuração no Frontend (React)

No seu arquivo onde você configura o Axios (ex: `src/services/api.js`):

```javascript
import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'http://localhost:8000', // URL do backend
  withCredentials: true,            // IMPORTANTE: Habilita o envio de cookies
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
});

export default apiClient;
```

### Fluxo de Login Correto:

```javascript
// src/services/LoginService.ts ou auth.js

import apiClient from './api';

// 1. Obter o cookie CSRF ANTES do login
const getCsrfCookie = async () => {
  await apiClient.get('/sanctum/csrf-cookie');
};

// 2. Fazer login
export const login = async (email, password) => {
  try {
    // Primeiro obtém o cookie CSRF
    await getCsrfCookie();
    
    // Depois faz o login
    const response = await apiClient.post('/api/login', {
      email,
      password
    });
    
    return response.data; // { token: "..." }
  } catch (error) {
    console.error('Erro no login:', error);
    throw error;
  }
};

// 3. Usar o token nas próximas requisições
export const setAuthToken = (token) => {
  if (token) {
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    delete apiClient.defaults.headers.common['Authorization'];
  }
};
```

---

## 🧪 Testando o Login

### 1. Via Postman/Insomnia:

**Passo 1: Obter Cookie CSRF**
```
GET http://localhost:8000/sanctum/csrf-cookie
```

**Passo 2: Login**
```
POST http://localhost:8000/api/login
Content-Type: application/json

{
    "email": "admin@admin.com",
    "password": "12345678"
}
```

**Resposta esperada:**
```json
{
    "token": "1|abcdefghijklmnopqrstuvwxyz..."
}
```

### 2. Via Frontend (React):

```javascript
// Exemplo de uso em um componente React
const handleLogin = async (e) => {
  e.preventDefault();
  
  try {
    const data = await login(email, password);
    
    // Salvar o token
    localStorage.setItem('token', data.token);
    setAuthToken(data.token);
    
    // Redirecionar para a página principal
    navigate('/');
  } catch (error) {
    console.error('Erro no login:', error);
    alert('Email ou senha incorretos');
  }
};
```

---

## 🔍 Diagnosticando Problemas de CORS

### No Console do Navegador:

Se você ver erros como:
```
Access to XMLHttpRequest at 'http://localhost:8000/api/login' 
from origin 'http://localhost:5173' has been blocked by CORS policy
```

**Isso significa que:**
- O backend não está aceitando requisições do frontend
- As configurações de CORS não estão corretas

### Verificar no Backend:

```bash
# Ver as configurações atuais
php artisan tinker

# No tinker:
config('cors.allowed_origins');
config('sanctum.stateful');
```

**Saída esperada:**
```php
// allowed_origins
=> [
     "http://localhost:5173",
     "http://127.0.0.1:5173",
   ]

// stateful
=> [
     "localhost",
     "localhost:3000",
     "localhost:5173",
     "127.0.0.1",
     "127.0.0.1:8000",
     "127.0.0.1:5173",
     "::1",
   ]
```

---

## 🚨 Problemas Comuns e Soluções

### 1. "CSRF token mismatch"

**Solução:**
- Certifique-se de chamar `/sanctum/csrf-cookie` ANTES do login
- Verifique se `withCredentials: true` está configurado no Axios

### 2. "Unauthenticated" nas rotas protegidas

**Solução:**
- Certifique-se de enviar o token no header:
  ```javascript
  headers: {
    'Authorization': `Bearer ${token}`
  }
  ```

### 3. "OPTIONS request blocked"

**Solução:**
- O navegador faz uma requisição OPTIONS (preflight) antes da real
- Certifique-se de que `'allowed_methods' => ['*']` está no `cors.php`
- Limpe o cache: `php artisan config:clear`

### 4. Login funciona no Postman mas não no navegador

**Solução:**
- Isso é um problema de CORS
- Verifique se o `SESSION_DOMAIN` no `.env` está correto
- Verifique se o frontend está na lista de `stateful domains`

---

## 📝 Checklist Final

- [ ] Arquivo `.env` atualizado com `FRONTEND_URL` e `SESSION_DOMAIN`
- [ ] Cache do Laravel limpo (`php artisan config:clear`)
- [ ] Servidor reiniciado
- [ ] Frontend configurado com `withCredentials: true`
- [ ] Chamada para `/sanctum/csrf-cookie` antes do login
- [ ] Token sendo enviado no header `Authorization`
- [ ] CORS configurado corretamente em `config/cors.php`
- [ ] Domínios stateful configurados em `config/sanctum.php`

---

## 🎯 Teste Rápido

Execute este código no console do navegador (F12) na página do seu frontend:

```javascript
// Teste 1: CORS está funcionando?
fetch('http://localhost:8000/api/login', {
  method: 'OPTIONS',
  credentials: 'include'
})
.then(res => console.log('CORS OK:', res.status))
.catch(err => console.error('CORS ERROR:', err));

// Teste 2: CSRF Cookie
fetch('http://localhost:8000/sanctum/csrf-cookie', {
  credentials: 'include'
})
.then(res => console.log('CSRF OK:', res.status))
.catch(err => console.error('CSRF ERROR:', err));
```

Se ambos retornarem status 200 ou 204, o CORS está funcionando! 🎉
