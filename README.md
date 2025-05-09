# Figma

 [🔗 Veja o protótipo no Figma](https://www.figma.com/design/gHGX7tGty8VM8reyGkj96e/Untitled?node-id=1-317&embed-host=share)
!  


# Aviso: Login com Google em Manutenção

> **⚠️ Atenção**  
> O login com Google está **em manutenção**.
>
> :: Aviso prévio para ninguém mexer nele, pois poderá influenciar no restante do código. Então, **tira o dedo**.

---

## Atualização do Alerta

O login com Google foi **temporariamente removido**, junto com a pasta `vendor` do `login.php`.  
Como **não haverá tempo hábil para resolver**, o **dark mode** foi **temporariamente desativado** para todas as páginas.  
A produção do modo escuro foi **levemente atrasada**, e sua integração não será feita agora.

Por esse motivo, ele será testado **apenas na página `user.php`**, onde o usuário pode ter uma ideia de como seria o **modo escuro / alternância de tema**.

---

# Documentação do Projeto de Vida

## :: Projeto de Vida

> Meu projeto de vida é baseado no que eu penso sobre meu futuro acadêmico.

---

## Visão Geral do Projeto

O **"Projeto de Vida"** é uma aplicação web desenvolvida em **PHP** que permite aos usuários documentar, planejar e acompanhar seu **desenvolvimento pessoal e profissional**.  
O projeto segue a arquitetura **MVC (Model-View-Controller)** e inclui funcionalidades de:

- Autoconhecimento
- Planejamento de carreira
- Definição de metas

---

## Estrutura do Banco de Dados

O banco de dados está estruturado com as seguintes tabelas principais:

### 1. `users`

- Dados básicos dos usuários: `username`, `email`, `senha`, `foto de perfil`
- Descrição pessoal
- Acesso ao sistema com autenticação segura (hash de senha)

### 2. `quem_sou_eu`

- Informações de autoconhecimento:
    - Características pessoais (forças e fraquezas)
    - Valores e aptidões
    - Relacionamentos (família, amigos, escola, sociedade)
    - Preferências, rotinas e vida escolar
    - Autoimagem e percepção dos outros
    - Índice de autovalorização

### 3. `teste_personalidade`

- Resultados de testes de personalidade (ex: MBTI)
- Mede:
    - Extroversão
    - Intuição
    - Racionalidade
    - Julgamento

### 4. `plano_acao`

- Criação de metas específicas:
    - Título
    - Descrição
    - Prazo
    - Status de conclusão

### 5. `landing_pages`

- Criação de páginas personalizadas:
    - Título
    - Subtítulo
    - Sobre
    - Educação
    - Carreira
    - Contato
- Controle de visibilidade: pública ou privada

### 6. `feedback`

- Armazena mensagens enviadas ao time do projeto:
    - Nome
    - Email
    - Assunto
    - Mensagem
    - Data

---

## Arquitetura e Tecnologias

- **Backend**: PHP 8.2.12
- **Banco de Dados**: MariaDB 10.4.32
- **Frontend**: HTML, CSS, JavaScript
- **Arquitetura**: MVC
- **Segurança**: Senhas com hash `bcrypt`

---

## Funcionalidades Principais

### 1. Sistema de Autenticação

- Registro e login de usuários
- Integração com login do Google (em manutenção)
- Senhas seguras

### 2. Perfil do Usuário

- Upload de foto de perfil
- Banner personalizado
- Informações e descrição pessoal
- Edição de perfil

### 3. Autoconhecimento

- Questionário "Quem Sou Eu"
- Teste de personalidade (MBTI ou similar)
- Avaliação de traços e aptidões

### 4. Planejamento

- Planos de ação
- Metas com prazos e status
- Acompanhamento de objetivos

### 5. Landing Page Pessoal

- Página de apresentação
- Seções customizáveis
- Opção de tornar pública ou privada

### 6. Sistema de Feedback

- Canal de contato com os administradores
- Mensagens categorizadas por assunto

### 7. Interface Responsiva

- CSS adaptativo para mobile e desktop
- Elementos visuais como banners e fotos
- Implementação parcial do dark mode

---

## Recursos Visuais

- Banner de perfil
- Foto de perfil
- Ícones e elementos gráficos diversos

---

## Integrações Planejadas

- Login com Google (em manutenção)
- Possíveis futuras integrações externas

---

## Análise e Pontos de Melhoria

### Pontos Fortes

1. **Arquitetura MVC bem definida**
2. **Banco de dados bem estruturado**
3. **Foco no desenvolvimento pessoal**
4. **Segurança com hash de senha**

### Melhorias Sugeridas

1. Concluir a **integração com o login do Google**
2. Aplicar **dark mode** em todas as páginas
3. Otimizar o **CSS** (ex: banner de perfil)
4. Expandir funcionalidades futuras

---

## Conclusão

O **Projeto de Vida** é uma aplicação web sólida voltada para o **autodesenvolvimento acadêmico e profissional**.  
Utiliza boas práticas como a arquitetura **MVC**, segurança com **hash**, e uma base de dados bem definida.

Combina ferramentas de **autoconhecimento, planejamento e apresentação pessoal**, e pode ser uma **ferramenta significativa para estudantes e profissionais** ao estruturar metas de vida e carreira.

Com a resolução das pendências (ex: login Google e dark mode completo), o projeto tem grande potencial de expansão e impacto.
