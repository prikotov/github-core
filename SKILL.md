---
name: github-core
description: Библиотека и каталог skills для GitHub API
license: MIT
compatibility: opencode
---

## Доступные skills

### github-traffic
Просмотры и клоны репозиториев:
- Просмотров за 14 дней
- Клонов за 14 дней
- Уникальные посетители

### github-referrers
Источники трафика:
- Популярные рефереры
- Популярные пути

### github-paths
Популярные страницы:
- Топ файлов/страниц
- Просмотров и уникальные посетители

### github-stars
Динамика звёзд:
- История звёзд по датам
- Топ репозиториев по звёздам

---

## Установка

1. Создайте файл `.opencode/skills/github-core/config.json`:

```json
{
  "token": "ghp_xxxxxxxxxxxxxxxxxxxx",
  "default_repo": "username/repo",
  "repos": {
    "my-project": "username/my-project",
    "another": "username/another-repo"
  }
}
```

2. Получите GitHub Personal Access Token:
   - https://github.com/settings/tokens
   - Права: `repo` (для приватных) или `public_repo` (для публичных)

3. Добавьте в `.gitignore`:
```
.opencode/skills/github-core/config.json
```

---

## Формат отчётов

Отчёты сохраняются в `github_reports/YYYY-MM-DD/`:
- CSV для данных
- MD для просмотра
