# Installed Agent Skills Documentation

This document records the Agent Skills installed for the **LagunaWISE — Ideology Division Website** project, following the open Agent Skills standard supported by Google Antigravity.

---

## 1. PHP Development (`php-development`)
* **Repository Source**: `https://github.com/PracticalSwan/agent-skills`
* **Local Path**: `.agents/skills/php-development/SKILL.md`
* **Target Version**: PHP 8.1+ / PHP 8.2 (Running on local XAMPP 8.2.12)
* **Core Capabilities**:
  - Secure database access via PHP Data Objects (PDO) with prepared statements
  - Clean separation of concerns with modular includes and route controllers
  - Secure authentication flows: `password_hash()`, `password_verify()`, session management, regeneration
  - XAMPP Apache configuration (`.htaccess`, error reporting, clean URLs)
  - Data validation and sanitization standards

---

## 2. Frontend Engineering (`frontend-skill`)
* **Repository Source**: `https://github.com/AIwithSakthivel/agentic-skills`
* **Local Path**: `.agents/skills/frontend-skill/SKILL.md`
* **Core Capabilities**:
  - Semantic HTML5 structure and clean hierarchy
  - Modern utility-first styling with Tailwind CSS & custom design tokens
  - Vanilla JavaScript for lightweight, reactive client interactions (modals, toasts, bookmarks, tabs)
  - Mobile-first responsive UI patterns for 360px up to 1920px viewports
  - Performance-conscious rendering without heavy JS frameworks

---

## 3. Web Application Security (`web-application-security`)
* **Repository Source**: `https://github.com/Auriti-Labs/agentwork`
* **Local Path**: `.agents/skills/web-application-security/SKILL.md`
* **Core Capabilities**:
  - Defensive security review and architecture
  - SQL injection prevention (strictly parameterized PDO queries)
  - Cross-Site Scripting (XSS) mitigation via contextual HTML escaping (`htmlspecialchars`)
  - Cross-Site Request Forgery (CSRF) token generation and validation for all mutating operations
  - Role-Based Access Control (RBAC) enforced strictly at server-side endpoints
  - Secure file upload validation (MIME-type checks, file size limits, extension whitelisting, non-executable storage)
  - Safe session handling (`HttpOnly`, `SameSite=Lax`, strict session fixation prevention)

---

## 4. Accessibility and UI Engineering (`accessibility`)
* **Repository Source**: `https://github.com/PracticalSwan/agent-skills`
* **Local Path**: `.agents/skills/accessibility/SKILL.md`
* **Core Capabilities**:
  - WCAG 2.2 Level AA compliance verification
  - Keyboard focus indicators and logical navigation paths
  - ARIA landmark roles and descriptive labels
  - High-contrast color verification against LagunaWISE brand tokens

---

## 5. Frontend Design (Anthropic) (`frontend-design`)
* **Repository Source**: `https://github.com/anthropics/skills/tree/main/skills/frontend-design`
* **Local Path**: `.agents/skills/frontend-design/SKILL.md`
* **License**: Apache 2.0 (`LICENSE.txt`)
* **Core Capabilities**:
  - Distinctive, intentional visual design guidance when building new UI or reshaping existing layouts
  - Avoidance of generic templates, generic corporate AI aesthetics, and predictable visual clichés
  - Subject-grounded design, opinionated typography pairings, and deliberate whitespace distribution
  - Responsive hero section craftsmanship tailored to specific brand identity and subject matter

---

## 6. Software Architecture (`architecture`)
* **Repository Source**: `https://github.com/sickn33/agentic-awesome-skills/tree/main/skills/architecture`
* **Local Path**: `.agents/skills/architecture/SKILL.md`
* **Core Capabilities**:
  - Architectural decision-making framework and Architecture Decision Records (ADRs)
  - Context discovery: requirements analysis, project classification, and quality attribute trade-offs
  - Pattern selection decision trees: modular monolith, tiered separation of concerns, and security layers
  - Trade-off analysis: balancing simplicity, maintainability, performance, and operational constraints
