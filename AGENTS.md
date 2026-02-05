# AGENTS.md – WordPress Onepage Plugin

Este repositório contém um **plugin WordPress de modelo onepage institucional**, totalmente configurável via painel administrativo.

## 🎯 Objetivo do Agente
- Melhorar e evoluir o plugin sem quebrar compatibilidade
- Manter o plugin reutilizável para outras instituições
- Garantir personalização total via painel
- Preservar performance e boas práticas WordPress

---

## 🧱 Arquitetura

- Plugin WordPress (PHP 8+)
- HTML sem framework
- CSS próprio (mobile-first)
- JavaScript vanilla
- Templates separados por bloco

---

## 🧩 Regras Importantes

### WordPress
- ❌ Nunca editar core do WordPress
- ❌ Nunca depender de tema específico
- ❌ Não usar page builders
- ✅ Usar hooks e filtros
- ✅ Usar Settings API
- ✅ Usar `wp_enqueue_scripts`

---

### Segurança
- Sempre sanitizar inputs:
  - `sanitize_text_field`
  - `sanitize_textarea_field`
  - `absint`
  - `esc_url_raw`
- Sempre escapar outputs:
  - `esc_html`
  - `esc_attr`
  - `wp_kses_post`
- Usar nonces quando aplicável

---

### CSS / Layout
- Mobile-first
- Sem `width` fixa
- Usar Flexbox ou Grid
- Prefixar classes com o namespace do plugin
- Evitar CSS global

---

### Templates
- Cada seção deve ter seu próprio arquivo
- Templates devem ser simples e reutilizáveis
- Nenhum conteúdo fixo (hardcoded)
- Tudo deve vir das configurações do painel

---

### Admin
- Toda configuração deve ser feita pelo painel
- Nenhum texto ou imagem deve depender de edição em código
- Preferir campos simples (texto, textarea, imagem, cor)

---

## 🛠️ Diretrizes para Evoluções

Quando solicitado:
- Propor plano antes de executar
- Fazer mudanças incrementais
- Evitar refatorações desnecessárias
- Manter retrocompatibilidade
- Documentar alterações relevantes

---

## 📦 Empacotamento

- O plugin deve permanecer instalável via ZIP
- Não incluir arquivos temporários
- Não incluir dependências externas desnecessárias

