# SoftNeos Presupuestos

Monorepo: `backend/` (Laravel 13, PHP 8.3+, SQLite, Sanctum) y `frontend/` (Angular 22 como Web Components dentro de vistas Blade, Bootstrap 5.3, SCSS).
La especificación completa está en `docs/SPEC.md`. Léela antes de cada fase.

## Modo de trabajo (obligatorio)
- Trabajamos FASE A FASE según docs/SPEC.md. No empieces la siguiente fase sin que te lo pida.
- Antes de escribir código: resume en 5-10 líneas qué vas a hacer y qué ficheros tocarás, y espera mi OK.
- Después de cada paso: explícame el porqué de cada decisión como si me preparara para una entrevista técnica. Nombra el concepto del framework (p. ej. "Route Model Binding", "FormRequest", "standalone component").
- Propón el mensaje de commit; no hagas commit sin mi confirmación.
- Si hay varias opciones razonables, preséntalas con pros y contras y recomienda una.

## Convenciones
- Commits: Conventional Commits en inglés (feat:, fix:, refactor:, test:, docs:, chore:, style:), con scope (backend|frontend|repo). Un commit por cambio lógico.
- PHP: PSR-12, tipos estrictos en firmas, controllers delgados (validación en FormRequest, lógica en Services/Enums, salida en API Resources).
- Angular: standalone components, signals para estado, servicios para HTTP, Reactive Forms, nueva sintaxis de control flow (@if, @for).
- Estilos: SCSS; variables de marca en un único fichero de tokens; nada de colores hardcodeados en componentes.
- Código y nombres en inglés; textos de interfaz en catalán (toda la aplicación: Blade, API y Angular).
- Dinero: columnas decimal, cast decimal:2, nunca float.

## Comandos
- Backend: `cd backend && php artisan serve` | `npm run dev` (Vite) | `php artisan test` | `php artisan migrate:fresh --seed`
- Frontend: `cd frontend && npm start` (ng build --watch hacia backend/public/angular) | `npm run build`