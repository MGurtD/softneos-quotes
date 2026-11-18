# SoftNeos Presupuestos – Especificación y guía de implementación

Nov 17, 2026 · @Marc Gurt Dot

## Cómo usar este documento

Este documento es a la vez la especificación que lee Claude Code y tu guía de estudio. Se implementa **fase a fase**, y cada fase sigue el mismo ciclo de 5 pasos:

1. **Concepto.** Lee la sección «Qué debes entender» antes de lanzar el prompt. Si no sabrías explicarlo en voz alta, pregúntale a Claude antes de generar código.
2. **Prompt.** Copia el prompt de la fase en Claude Code. Claude Code ya tiene este documento en `docs/SPEC.md` y las reglas en `CLAUDE.md`.
3. **Verificar.** Ejecuta tú mismo los comandos de verificación. No te fíes de que «ya funciona».
4. **Explicar.** Pide a Claude Code que te explique el diff (`git diff --stat` y los ficheros clave). Responde en voz alta las preguntas de la fase como si fuera la entrevista.
5. **Commit.** Revisa el mensaje propuesto y haz el commit. Un commit por paso lógico, nunca uno gigante por fase.

Regla de oro: **no avances de fase sin poder dibujar en una servilleta qué ficheros ha tocado y por qué.** En la revisión en directo te pedirán cambios. Lo que te salvará es saber por qué capas pasa cada cambio, no recordar el código.

## Correcciones de alcance (18-11-2026)

Contrastada la SPEC con el enunciado literal (`docs/ENUNCIAT.md`), prevalecen estas decisiones sobre lo que diga el resto del documento:

- **Empresa:** SoftNeos, con textos propios. Se toma su paleta y tipografía para que resulte familiar, pero no se copian eslóganes, productos ni maquetación de sus webs.
- **Web corporativa en Blade: una sola página** (hero, quiénes somos, módulos de la Suite, nuevo módulo de presupuestos, contacto) en lugar de tres. Sustituye a las rutas `/suite` y `/contacto`.
- **Contenido desde base de datos en la parte pública:** los módulos de la Suite salen de la tabla `suite_modules`. El enunciado lo valora «de manera important» y debe verse sin iniciar sesión.
- **Blade:** web corporativa, login y vista imprimible. **Angular:** gestión de presupuestos (sin cambios).
- **Idioma:** toda la interfaz en catalán.

## Visión y alcance

Web corporativa de SoftNeos con una landing pública en Blade y un área privada en Angular para gestionar presupuestos de clientes, como módulo ligado a SoftNeos Suite.

**Enunciado (resumen):** web corporativa desde cero en localhost con la última versión estable de Angular, Bootstrap y Laravel (PHP 8.2 o superior). Se hace con vistas Blade **y** con vistas Angular. Se valora que parte del contenido venga de base de datos, el diseño (SCSS), el clean code, la calidad de los commits y la documentación. Entrega: repositorio Git, README con instalación, `.env.example` y capturas, y una declaración de uso de IA.

**Stack objetivo** (comprobar el día de la Fase 0):

| Pieza | Versión | Nota |
| --- | --- | --- |
| Laravel | 13.x | Requiere PHP 8.3, que cumple «8.2 o superior» |
| PHP | 8.3 o 8.4 | `php -v` |
| Angular | 22.x | La v23 no sale hasta junio de 2027 |
| Bootstrap | 5.3.x | `npm view bootstrap version` |
| Base de datos | SQLite | Un fichero, cero instalación |
| Auth | Laravel Sanctum (modo SPA, cookie) |  |

**Entra en el alcance:**

- Landing pública en Blade (Inicio, SoftNeos Suite, Contacto) y login en Blade.
- SPA Angular en `/app`: presupuestos (CRUD completo con líneas), clientes (listado y alta), referencias (listado y alta).
- Cálculo de totales con desglose de IVA en backend.
- Vista imprimible de un presupuesto en Blade.
- Tests de la lógica de negocio y de la API.

**Queda fuera (decisión consciente, documentada en el README):** registro de usuarios, roles y permisos, generación de PDF, envío de emails, multiempresa y despliegue.

## Arquitectura

Un monorepo con un backend Laravel que sirve la web pública en Blade y una API JSON, y una SPA Angular que consume esa API reutilizando la sesión creada en el login de Blade.

&#91;embedded content: arquitectura · Blade y SPA sobre una sola sesión de Laravel\]

El login se hace en Blade y crea una cookie de sesión `HttpOnly`. Como las cookies no distinguen puertos, la SPA en `:4200` la reutiliza a través del proxy de `ng serve`, y Sanctum la acepta en `/api`. Toda la lógica de negocio está en la capa de dominio, compartida por Blade (vista imprimible) y por la API.

**Estructura del repositorio:**

```text
softneos-presupuestos/
├── backend/            Laravel 13 (Blade + API + Sanctum + SQLite)
├── frontend/           Angular 22 (SPA del área privada)
├── design/_tokens.scss Variables de marca compartidas por ambos
├── docs/SPEC.md        Este documento
├── docs/AI_USAGE.md    Declaración de uso de IA
├── docs/screenshots/
├── CLAUDE.md
└── README.md
```

**Modos de ejecución:**

- **Desarrollo:** `php artisan serve` (:8000) + `npm run dev` de Vite para Blade + `ng serve` (:4200) con proxy. Tras el login se redirige a `FRONTEND_URL=http://localhost:4200`.
- **Demo (Fase 8):** el build de Angular se copia a `backend/public/app` y Laravel lo sirve en `/app`. El evaluador solo arranca `php artisan serve`.

## Modelo de datos

Cinco tablas de negocio más `users`. La línea de presupuesto **copia** precio, descripción e IVA en el momento de crearla (snapshot), para que un cambio posterior en la referencia o en la ley no altere presupuestos ya emitidos.

| Tabla (modelo) | Campos | Relaciones y notas |
| --- | --- | --- |
| `vat_types` (`VatType`) | `id`, `name` (General, Reducido, Superreducido, Exento), `rate` decimal(5,2) (21, 10, 4, 0), `active` bool | Catálogo. Los tipos cambian por ley; se gestionan en un único sitio. |
| `customers` (`Customer`) | `id`, `name`, `tax_id` (NIF/CIF, único), `email`, `phone`, `address`, `city`, `postal_code`, timestamps | hasMany `Quote` |
| `product_references` (`ProductReference`) | `id`, `code` (único), `description`, `unit` (pza, kg, h, m), `unit_price` decimal(12,2), `vat_type_id` FK, `active` bool, timestamps | belongsTo `VatType` (IVA por defecto). **No** se llama `references`: es palabra reservada de SQL. |
| `quotes` (`Quote`) | `id`, `number` (P-2026-0001, único), `customer_id` FK, `issue_date`, `valid_until`, `status` (enum), `notes` text nullable, timestamps | belongsTo `Customer`, hasMany `QuoteLine`. Borrar un presupuesto borra sus líneas (cascade). |
| `quote_lines` (`QuoteLine`) | `id`, `quote_id` FK cascade, `position` int, `product_reference_id` FK nullable, `description`, `quantity` decimal(12,3), `unit_price` decimal(12,2), `discount_pct` decimal(5,2) default 0, `vat_type_id` FK, `vat_rate` decimal(5,2), timestamps | belongsTo `Quote`, `ProductReference`, `VatType`. `description`, `unit_price` y `vat_rate` son **copias**. La referencia es opcional para permitir líneas libres. |
| `users` (`User`) | Los de Laravel por defecto | Un usuario demo por seeder. |

Decisiones que debes saber defender:

- **Dinero en `decimal`, nunca en `float`.** Los float no representan 0,10 exacto. En Eloquent se usa el cast `decimal:2`, que devuelve un string; los cálculos se redondean a 2 decimales por línea.
- **`vat_type_id` + `vat_rate` en la línea:** el FK dice qué tipo se eligió; el `vat_rate` congela el porcentaje aplicado.
- **`position`** permite ordenar las líneas como las ve el usuario.
- **Totales no guardados** en `quotes`: se calculan siempre (fuente única de verdad). Si te preguntan por rendimiento: con este volumen no compensa desnormalizar; si hiciera falta, se guardarían al cambiar de estado a `sent`.

## Reglas de negocio

Toda regla vive en el backend; Angular solo muestra una previsualización mientras se edita.

**Cálculo por línea** (redondeo a 2 decimales en cada paso, *half up*):

```latex
\text{base} = \text{round}\big(\text{quantity} \cdot \text{unit\_price} \cdot (1 - \tfrac{\text{discount\_pct}}{100}),\,2\big) \qquad \text{cuota} = \text{round}\big(\text{base} \cdot \tfrac{\text{vat\_rate}}{100},\,2\big)
```

**Totales del presupuesto:**

- Desglose por tipo de IVA: se agrupan las líneas por `vat_rate` y se suman base y cuota de cada grupo (ejemplo: base al 21 % 1.000,00 → cuota 210,00; base al 10 % 200,00 → cuota 20,00).
- `subtotal` = suma de bases; `vat_total` = suma de cuotas; `total` = `subtotal` + `vat_total`.
- Se implementa en una clase `App\Services\QuoteCalculator` (o un método `Quote::totals()`) que devuelve un objeto `QuoteTotals`. Un solo punto, cubierto por tests unitarios.

**Numeración:** `P-{año}-{secuencia de 4 dígitos}` (P-2026-0001), correlativa por año. La asigna el backend al crear, dentro de una transacción. Nunca la envía el cliente.

**Al añadir una línea con referencia:** el backend copia `description`, `unit_price` y `vat_type_id` de la referencia si no vienen informados, y siempre copia `vat_rate` del tipo de IVA elegido. El usuario puede sobrescribir descripción, precio, descuento e IVA.

**Estados** (`App\Enums\QuoteStatus`, enum PHP respaldado por string):

| Estado | Editable | Puede pasar a |
| --- | --- | --- |
| `draft` (Borrador) | Sí: cabecera y líneas | `sent` |
| `sent` (Enviado) | No | `accepted`, `rejected`, `draft` (reabrir) |
| `accepted` (Aceptado) | No | — |
| `rejected` (Rechazado) | No | `draft` (reabrir) |

- Solo se puede **borrar** un presupuesto en `draft`.
- Una transición no permitida devuelve **422** con mensaje claro. La regla vive en el enum (`canTransitionTo()`), no en el controller.
- No se puede pasar a `sent` un presupuesto sin líneas.

**Validaciones mínimas:** `quantity` > 0; `unit_price` ≥ 0; `discount_pct` entre 0 y 100; `valid_until` ≥ `issue_date`; `tax_id` y `code` únicos; al menos una línea al guardar un presupuesto.

## Contrato de la API y rutas web

Todas las rutas `/api/*` exigen sesión (`auth:sanctum`) y responden JSON con API Resources. Los errores de validación devuelven 422 con el formato estándar de Laravel (`message` + `errors`).

**Rutas web (Blade, `routes/web.php`):**

| Método | Ruta | Qué hace | Acceso |
| --- | --- | --- | --- |
| GET | `/` | Landing: hero, qué es SoftNeos, sectores | Público |
| GET | `/suite` | Producto SoftNeos Suite y módulo de presupuestos | Público |
| GET | `/contacto` | Datos de contacto | Público |
| GET / POST | `/login` | Formulario y `Auth::attempt` | Invitado |
| POST | `/logout` | Cierra sesión | Autenticado |
| GET | `/quotes/{quote}/print` | Vista imprimible del presupuesto (Blade + BD) | Autenticado |
| GET | `/app/{any?}` | Sirve el `index.html` del build de Angular (opcional, Fase 8) | Autenticado |

**API (`routes/api.php`):**

| Método | Ruta | Qué hace |
| --- | --- | --- |
| GET | `/api/user` | Usuario de la sesión (lo usa el guard de Angular) |
| GET | `/api/vat-types` | Tipos de IVA activos |
| GET, POST | `/api/customers` | Listado (búsqueda `?q=`, paginado) y alta |
| GET, PUT | `/api/customers/{id}` | Detalle y edición |
| GET, POST | `/api/product-references` | Listado (`?q=`, `?active=1`) y alta |
| GET, PUT | `/api/product-references/{id}` | Detalle y edición |
| GET | `/api/quotes` | Listado paginado; filtros `?status=` y `?customer_id=` |
| POST | `/api/quotes` | Crea cabecera + líneas en una transacción |
| GET | `/api/quotes/{id}` | Detalle con cliente, líneas y totales |
| PUT | `/api/quotes/{id}` | Actualiza cabecera y **reemplaza** las líneas (solo `draft`) |
| DELETE | `/api/quotes/{id}` | Borra (solo `draft`) |
| PATCH | `/api/quotes/{id}/status` | Cambia estado validando la transición |

**Ejemplo de respuesta de `GET /api/quotes/1`:**

```json
{
  "data": {
    "id": 1,
    "number": "P-2026-0001",
    "status": { "value": "draft", "label": "Borrador" },
    "issue_date": "2026-11-17",
    "valid_until": "2026-12-17",
    "customer": { "id": 3, "name": "Mecanitzats Exemple SL", "tax_id": "B12345678" },
    "lines": [
      { "id": 10, "position": 1, "product_reference_id": 5, "description": "Mecanizado pieza A-200",
        "quantity": "10.000", "unit_price": "45.00", "discount_pct": "5.00",
        "vat_type_id": 1, "vat_rate": "21.00", "base": "427.50", "vat_amount": "89.78" }
    ],
    "totals": {
      "vat_breakdown": [ { "rate": "21.00", "base": "427.50", "vat": "89.78" } ],
      "subtotal": "427.50", "vat_total": "89.78", "total": "517.28"
    }
  }
}
```

**Cuerpo de `POST/PUT /api/quotes`:** `customer_id`, `issue_date`, `valid_until`, `notes`, `lines[]` con `product_reference_id?`, `description`, `quantity`, `unit_price`, `discount_pct`, `vat_type_id`. Nunca `number`, `status`, `vat_rate` ni totales: los decide el servidor.

## CLAUDE.md para el repositorio

Copia este bloque a `CLAUDE.md` en la raíz del repo en la Fase 0. Es lo que convierte a Claude Code en tutor y no solo en generador de código. Guarda este documento entero como `docs/SPEC.md`.

```markdown
# SoftNeos Presupuestos

Monorepo: `backend/` (Laravel 13, PHP 8.3+, SQLite, Sanctum) y `frontend/` (Angular 22, Bootstrap 5.3, SCSS).
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
- Código y nombres en inglés; textos de interfaz en español.
- Dinero: columnas decimal, cast decimal:2, nunca float.

## Comandos
- Backend: `cd backend && php artisan serve` | `php artisan test` | `php artisan migrate:fresh --seed`
- Frontend: `cd frontend && npm start` (ng serve con proxy a :8000) | `npm test` | `npm run build`
```

**Dos ayudas para Claude Code que conviene activar en la Fase 0:**

- **Laravel Boost:** paquete de desarrollo de Laravel que da a agentes como Claude Code contexto del proyecto y documentación de la versión exacta. Se instala en `backend` con Composer.
- **MCP server de Angular CLI:** el CLI de Angular incluye un servidor MCP para agentes (estable desde la v21). Se añade a Claude Code con `claude mcp add angular-cli -- npx -y @angular/cli mcp`.

Así Claude Code genera código de Laravel 13 y Angular 22, no de versiones viejas de su entrenamiento. Menciónalo en la declaración de IA: demuestra criterio.

## Fase 0 · Entorno y repositorio

**Objetivo:** máquina preparada y repo vacío con `README.md`, `CLAUDE.md`, `docs/SPEC.md` y `.gitignore`. Tiempo estimado: 30 min.

**Qué debes entender:**

- **Monorepo:** un solo repositorio con dos aplicaciones. Ventajas: un historial de commits, un README y cambios que cruzan backend y frontend en un mismo commit. Inconveniente: dos toolchains (Composer y npm).
- **Composer vs npm:** gestores de dependencias de PHP y JavaScript. `composer.lock` y `package-lock.json` **sí** se versionan; `vendor/` y `node_modules/` **no**.

**Comprobaciones previas (hazlas tú):**

```bash
php -v              # 8.3 o superior, con extensiones pdo_sqlite, mbstring, openssl
composer -V
node -v             # versión LTS compatible con Angular 22 (ver angular.dev/reference/versions)
npm -v
npx @angular/cli version
npm view bootstrap version
git --version
```

**Prompt para Claude Code:**

```text
Lee docs/SPEC.md. Vamos a hacer la Fase 0.
Crea la estructura del monorepo: carpetas backend/ y frontend/ vacías (con .gitkeep), .gitignore raíz
(vendor, node_modules, .env, database.sqlite, dist, .angular), README.md con un esqueleto de secciones
(Descripción, Stack, Arquitectura, Instalación, Uso, Tests, Decisiones, Capturas, Uso de IA)
y CLAUDE.md con el contenido de la sección "CLAUDE.md para el repositorio" de la spec.
Inicializa git en la rama main. Explícame qué entra y qué no en el .gitignore y por qué.
```

**Verificación:** `git status` muestra solo los ficheros esperados.

**Commit:** `chore(repo): initialize monorepo structure and project docs`

Después: crea el repo en GitHub (público o privado compartido con el evaluador) y haz el primer `push`. Empujar desde el día 1 deja un historial con fechas creíbles.

**Preguntas que te pueden hacer:**

- ¿Por qué monorepo y no dos repos? → Prueba pequeña, un solo entregable, commits atómicos que tocan API y cliente a la vez.
- ¿Por qué versionas los lockfiles? → Builds reproducibles: el evaluador instala exactamente las mismas versiones que tú.

## Fase 1 · Backend base: Laravel, SQLite y Sanctum

**Objetivo:** Laravel arrancando en `localhost:8000` con SQLite, Sanctum en modo SPA y un `.env.example` limpio. Tiempo estimado: 1 h.

**Qué debes entender:**

- **Ciclo de una petición:** `public/index.php` → `bootstrap/app.php` (registra rutas y middleware) → middleware (sesión, CSRF, auth) → ruta → controller → modelo Eloquent → respuesta (vista Blade o JSON). Este es el mapa mental que usarás en todos los cambios en directo.
- **`bootstrap/app.php`:** desde Laravel 11 sustituye al antiguo `Kernel.php`. Ahí se configuran las rutas (`web`, `api`), el middleware y las excepciones.
- **`.env` vs `.env.example`:** `.env` tiene valores de tu máquina y secretos (no se versiona). `.env.example` es la plantilla documentada (sí se versiona). `APP_KEY` se genera con `php artisan key:generate`.
- **`php artisan install:api`:** crea `routes/api.php` e instala Sanctum.
- **Sanctum en modo SPA:** `statefulApi()` en `bootstrap/app.php` hace que las peticiones a `/api` que llegan desde un dominio «de confianza» (`SANCTUM_STATEFUL_DOMAINS`) usen la **sesión por cookie** en lugar de tokens.

**Prompt para Claude Code:**

```text
Fase 1 de docs/SPEC.md. Crea el proyecto Laravel 13 en backend/ (composer create-project).
- Base de datos SQLite (database/database.sqlite), sesiones en base de datos.
- Ejecuta install:api para tener routes/api.php y Sanctum.
- Configura Sanctum en modo SPA con cookie: statefulApi() en bootstrap/app.php,
  SANCTUM_STATEFUL_DOMAINS=localhost:4200,localhost:8000, SESSION_DOMAIN=localhost, FRONTEND_URL=http://localhost:4200.
- Deja un .env.example completo y comentado con esas variables.
- APP_LOCALE=es y zona horaria Europe/Madrid.
Antes de empezar, explícame el ciclo de vida de una petición en Laravel 13 usando ficheros reales del proyecto.
Al acabar, explícame qué hace cada variable nueva del .env.example.
```

**Verificación:**

```bash
cd backend && php artisan migrate && php artisan serve   # http://localhost:8000 muestra la welcome de Laravel
php artisan route:list                                   # aparece GET api/user con middleware auth:sanctum
php artisan test                                         # tests de ejemplo en verde
```

**Commits:**

- `chore(backend): scaffold Laravel 13 application`
- `chore(backend): configure SQLite, locale and timezone`
- `feat(backend): install Sanctum and enable stateful SPA authentication`

**Preguntas que te pueden hacer:**

- ¿Por qué SQLite? → Cero instalación para el evaluador; cambiar a MySQL es tocar 5 variables del `.env`, porque Eloquent abstrae el motor.
- ¿Qué diferencia hay entre `web` y `api` en Laravel? → Grupos de middleware distintos: `web` lleva sesión y CSRF; `api` es sin estado salvo que Sanctum lo haga stateful para la SPA.

## Fase 2 · Migraciones, modelos, enums y seeders

**Objetivo:** el modelo de datos de la spec en base de datos, con relaciones Eloquent y datos de demo realistas del sector metal. Tiempo estimado: 2 h.

**Qué debes entender:**

- **Migración:** control de versiones del esquema. Cada fichero tiene `up()` y `down()`. El orden importa por las claves foráneas: `vat_types` antes que `product_references`, y `quotes` antes que `quote_lines`.
- **Modelo Eloquent:** una clase por tabla. `$fillable` protege contra *mass assignment*, `casts()` convierte tipos (fechas, decimales, enums) y los métodos `hasMany` / `belongsTo` definen las relaciones.
- **Enum respaldado (`QuoteStatus: string`):** PHP 8.1+. Con un cast en el modelo, `$quote->status` es un objeto enum, no un string. Así las reglas (`label()`, `isEditable()`, `canTransitionTo()`) viven en el propio enum.
- **Factory vs Seeder:** la factory sabe fabricar un registro falso; el seeder decide qué y cuántos se insertan. `migrate:fresh --seed` reconstruye todo desde cero.
- **Problema N+1:** cargar 20 presupuestos y luego el cliente de cada uno lanza 21 consultas. Se evita con *eager loading*: `Quote::with(['customer', 'lines'])`.

**Prompt para Claude Code:**

```text
Fase 2 de docs/SPEC.md, sección "Modelo de datos".
1. Migraciones para vat_types, customers, product_references, quotes y quote_lines exactamente como en la spec
   (decimales, únicos, FKs con cascade en quote_lines.quote_id, índices en FKs y en quotes.status).
2. Modelos con fillable, casts (decimal:2, date, enum) y relaciones.
3. Enum App\Enums\QuoteStatus con label() en español, isEditable() y canTransitionTo() según la tabla de estados.
4. Factories y DatabaseSeeder: los 4 tipos de IVA, 12 referencias del sector metal/mecanizado
   (mecanizado CNC, corte láser, plegado, horas de diseño...) con distintos IVA, 8 clientes con nombres de
   talleres ficticios, 15 presupuestos en estados variados con 2-6 líneas cada uno, y el usuario
   demo@softneos.test / password.
Hazlo en commits separados: migraciones, modelos+enum, seeders. Para cada uno, explícame los conceptos usados.
```

**Verificación:**

```bash
php artisan migrate:fresh --seed
php artisan tinker
>>> App\Models\Quote::with('customer','lines')->first()      # cliente y líneas cargados
>>> App\Models\Quote::first()->status                       # objeto QuoteStatus, no string
>>> App\Models\Quote::first()->status->label()              # "Borrador", "Enviado"...
```

**Commits:**

- `feat(backend): add migrations for customers, references, VAT types and quotes`
- `feat(backend): add Eloquent models, relationships and QuoteStatus enum`
- `feat(backend): add factories and demo seeders`

**Preguntas que te pueden hacer:**

- ¿Qué es *mass assignment* y cómo te proteges? → Rellenar un modelo con todo el `request()`; un atacante podría mandar `status=accepted`. `$fillable` limita qué campos se aceptan.
- ¿Cómo añadirías un campo? → Nunca editando una migración ya ejecutada: migración nueva con `addColumn`, `$fillable`, cast si aplica, FormRequest, Resource y vistas. Ver «Ensayo de cambios en directo».

## Fase 3 · Lógica de negocio y tests

**Objetivo:** cálculo de totales, numeración y transiciones de estado en clases propias, cubiertas por tests antes de exponer la API. Tiempo estimado: 1,5 h.

**Qué debes entender:**

- **Controllers delgados:** el controller orquesta (recibe, delega, responde). La lógica de dominio va en clases que se pueden testear sin HTTP: `QuoteCalculator`, `QuoteNumberGenerator`, el enum `QuoteStatus`.
- **Inyección de dependencias:** Laravel resuelve automáticamente las clases que pides en el constructor o en el método del controller (Service Container). Por eso puedes escribir `public function show(Quote $quote, QuoteCalculator $calc)`.
- **Tests unitarios vs de feature:** el unitario prueba una clase aislada (el cálculo); el de feature hace una petición HTTP simulada contra la app completa con una base de datos de test (`RefreshDatabase`).
- **Transacción:** `DB::transaction()` garantiza que la cabecera y todas las líneas se guardan juntas o no se guarda nada. También protege la numeración correlativa.

**Prompt para Claude Code:**

```text
Fase 3 de docs/SPEC.md, sección "Reglas de negocio".
1. App\Services\QuoteCalculator: calcula base y cuota por línea y los totales con desglose por vat_rate,
   redondeando a 2 decimales half-up en cada paso. Devuelve un DTO readonly QuoteTotals.
2. App\Services\QuoteNumberGenerator: P-{año}-{0001}, correlativo por año, seguro dentro de una transacción.
3. Completa QuoteStatus::canTransitionTo() según la tabla de estados.
4. Tests unitarios (PHPUnit o Pest, el que venga por defecto): línea con descuento, redondeo de 89.775 -> 89.78,
   desglose con dos tipos de IVA, línea exenta, numeración que reinicia al cambiar de año,
   todas las transiciones permitidas y prohibidas.
Escribe primero los tests, enséñamelos fallando, y luego la implementación. Explícame por qué esta lógica
no va en el controller ni en Angular.
```

**Verificación:** `php artisan test` en verde. Comprueba a mano el ejemplo de la spec: 10 × 45,00 con 5 % de descuento = 427,50 de base; 21 % = 89,78; total 517,28.

**Commits:**

- `test(backend): add specs for quote totals, numbering and status transitions`
- `feat(backend): implement QuoteCalculator with per-rate VAT breakdown`
- `feat(backend): implement quote numbering and status transition rules`

**Preguntas que te pueden hacer:**

- ¿Por qué no calculas el total en Angular? → El cliente no es de confianza y habría dos implementaciones que pueden divergir. Angular solo previsualiza; el valor bueno lo devuelve la API.
- ¿Por qué redondeas por línea? → Es como se hace en facturación: cada línea muestra importes de 2 decimales y la suma debe cuadrar con lo que el cliente ve impreso.

## Fase 4 · API REST

**Objetivo:** los endpoints de la spec, protegidos por Sanctum, con validación en FormRequests, salida en API Resources y tests de feature. Tiempo estimado: 3 h.

**Qué debes entender** (este es el núcleo de la entrevista en el lado Laravel):

- **`Route::apiResource`:** una línea genera las 5 rutas REST (`index`, `store`, `show`, `update`, `destroy`) apuntando a un controller.
- **Route Model Binding:** si la ruta es `/quotes/{quote}` y el método recibe `Quote $quote`, Laravel busca el registro por id y devuelve 404 si no existe. Sin código.
- **FormRequest:** clase con `rules()` (y opcionalmente `authorize()`). Si la validación falla, Laravel responde 422 con los errores antes de entrar en el controller. Para las líneas: `lines.*.quantity`.
- **API Resource:** decide la forma del JSON: qué campos, qué nombres, qué relaciones (`whenLoaded`). Desacopla la API de la estructura de la tabla.
- **Códigos HTTP:** 200 OK, 201 creado, 204 borrado, 401 sin sesión, 404 no existe, 422 validación o regla de negocio.

**Prompt para Claude Code:**

```text
Fase 4 de docs/SPEC.md, sección "Contrato de la API".
1. Rutas en routes/api.php bajo middleware auth:sanctum, con apiResource donde aplique y PATCH quotes/{quote}/status.
2. Controllers en App\Http\Controllers\Api, delgados: FormRequest para validar, servicios de la Fase 3 para la lógica,
   API Resources para la salida. Eager loading para evitar N+1.
3. Store/Update de quotes en DB::transaction: crea cabecera, reemplaza líneas, aplica los snapshots
   (description, unit_price, vat_type_id desde la referencia si no vienen; vat_rate siempre desde vat_types).
4. Reglas: solo draft es editable/borrable; transiciones inválidas -> 422 con mensaje en español.
5. QuoteResource debe devolver exactamente la forma del JSON de ejemplo de la spec, incluido totals.
6. Tests de feature: 401 sin sesión, crear presupuesto con 2 líneas y 2 IVA, 422 por validación,
   422 al editar un sent, cambio de estado válido e inválido, filtro por status.
Ve endpoint a endpoint. Después de QuotesController, párate y explícame el recorrido completo de
POST /api/quotes: ruta -> middleware -> FormRequest -> controller -> servicio -> modelo -> Resource.
```

**Verificación:**

```bash
php artisan route:list --path=api
php artisan test
```

Para probar a mano antes de tener Angular, inicia sesión en el navegador en `:8000` (Fase 5) o usa los tests de feature con `actingAs($user)`.

**Commits** (uno por recurso):

- `feat(backend): add VAT types and customers API endpoints`
- `feat(backend): add product references API endpoints`
- `feat(backend): add quotes API with lines, totals and status transitions`
- `test(backend): cover quotes API authorization, validation and business rules`

**Preguntas que te pueden hacer:**

- ¿Por qué PUT reemplaza todas las líneas en vez de hacer un diff? → Es más simple y atómico dentro de una transacción; el presupuesto es un agregado y se guarda entero. Con histórico o auditoría por línea, haría un *sync* por id.
- ¿Dónde validarías que solo se edite un borrador? → En una Policy o en el FormRequest (`authorize()` o regla), apoyándose en `QuoteStatus::isEditable()`. Nunca solo en el frontend.

## Fase 5 · Web pública en Blade, login y vista imprimible

**Objetivo:** landing corporativa de SoftNeos (3 páginas), login con sesión y la vista imprimible del presupuesto. Todo con Bootstrap personalizado por SCSS y compilado con Vite. Tiempo estimado: 3 h.

**Qué debes entender:**

- **Blade:** motor de plantillas que se compila a PHP. `{{ $x }}` escapa el HTML (protección XSS); `{!! $x !!}` no escapa y solo se usa con contenido de confianza.
- **Layouts con componentes:** `<x-layouts.public>` con `{{ $slot }}` es la forma moderna; `@extends` y `@section` es la clásica. Ambas son válidas y conviene saber las dos. Componentes pequeños: `<x-navbar>`, `<x-feature-card>`.
- **Formularios en Blade:** `@csrf` añade el token anti-CSRF; `@error('email')` y `old('email')` muestran errores y conservan lo escrito tras un 422.
- **Autenticación con sesión:** `Auth::attempt()` comprueba credenciales; `$request->session()->regenerate()` evita *session fixation*. Los middleware `guest` y `auth` protegen las rutas.
- **Vite en Laravel:** `@vite(['resources/scss/app.scss'])` inyecta el CSS compilado; `npm run dev` lo sirve en caliente.
- **Tokens de diseño compartidos:** un único `design/_tokens.scss` en la raíz del repo con colores, tipografías y radios. Lo importan Laravel y Angular **antes** de Bootstrap, sobrescribiendo sus variables (`$primary`, `$font-family-sans-serif`, `$border-radius`). Cambiar el color corporativo = cambiar una línea.

**Prompt para Claude Code:**

```text
Fase 5 de docs/SPEC.md, sección "Rutas web".
1. design/_tokens.scss en la raíz: paleta corporativa (primario azul industrial, acento, grises), tipografía,
   radios y sombras. En backend: bootstrap y sass por npm, resources/scss/app.scss que importa los tokens
   ANTES de bootstrap. Configura Vite para poder leer ../design.
2. Layout público como componente Blade (navbar con logo de texto SoftNeos, footer con contacto y avisos legales)
   y páginas: Inicio (hero "La nueva ingeniería del software", sectores: industria, ayuntamientos,
   entidades deportivas; productos), Suite (producción, costes, fichaje, conector universal, y el nuevo
   módulo de presupuestos con CTA "Acceso clientes"), Contacto. Textos inventados pero creíbles.
3. LoginController con Auth::attempt, regenerate, mensajes en español, rate limit de 5 intentos;
   tras el login, redirect a FRONTEND_URL. Logout por POST.
4. Ruta /quotes/{quote}/print (auth) con una vista Blade A4 imprimible: datos de SoftNeos, cliente,
   líneas, desglose de IVA y total, usando QuoteCalculator. CSS @media print.
5. Responsive y accesible: HTML semántico, alt en imágenes, contraste AA.
Un commit por punto. Explícame layouts vs componentes en Blade y por qué importamos los tokens antes que Bootstrap.
```

**Verificación:**

- `npm run dev` y `php artisan serve` en `backend/`: navega por las 3 páginas en móvil y escritorio (DevTools).
- Login con credenciales malas: error en español, el email se conserva. Con `demo@softneos.test` / `password`: redirige a `FRONTEND_URL`.
- `/quotes/1/print` sin sesión → redirige al login; con sesión → presupuesto listo para imprimir (Ctrl+P).
- Cambia `$primary` en `design/_tokens.scss` y comprueba que cambia todo el sitio.

**Commits:**

- `style(repo): add shared design tokens and Bootstrap SCSS setup`
- `feat(backend): add public corporate pages with Blade components`
- `feat(backend): add session login and logout`
- `feat(backend): add printable quote view`

**Preguntas que te pueden hacer:**

- ¿Por qué el login en Blade y no en Angular? → Es puro Laravel (CSRF, sesión, rate limit) y deja la cookie de sesión lista para que la SPA la reutilice con Sanctum. Blade tiene una función real, no decorativa.
- ¿Por qué no Bootstrap por CDN? → Para personalizar las variables SCSS en tiempo de compilación e incluir solo lo necesario. Por CDN solo podrías sobrescribir CSS final.

## Fase 6 · Angular: base, estilos y autenticación

**Objetivo:** SPA Angular 22 en `frontend/` con Bootstrap y los mismos tokens SCSS, proxy a Laravel, guard e interceptor de sesión y el *shell* de la aplicación (navbar y rutas vacías). Tiempo estimado: 2,5 h.

**Qué debes entender** (el núcleo de la entrevista en el lado Angular):

- **Standalone components:** cada componente declara sus propios `imports`; ya no hay `NgModule`. Desde Angular 20 la guía de estilo quita sufijos (`quote-list.ts` en vez de `quote-list.component.ts`); que no te sorprenda.
- **`app.config.ts`:** el punto de configuración global, con `provideRouter(routes)` y `provideHttpClient(withInterceptors([...]))`.
- **Signals:** estado reactivo. `signal()` guarda un valor, `computed()` deriva otro y la vista se actualiza sola. Angular es *zoneless* por defecto desde la v21: la detección de cambios se apoya en signals, no en Zone.js.
- **Routing con lazy loading:** `loadComponent: () => import(...)` descarga cada pantalla solo cuando se visita.
- **Guard funcional (`CanActivateFn`):** antes de entrar en `/quotes` llama a `GET /api/user`; si responde 401, envía al login de Laravel.
- **Interceptor funcional (`HttpInterceptorFn`):** se ejecuta en cada petición. El nuestro detecta 401 o 419 (sesión o CSRF caducados) y redirige al login.
- **XSRF:** `HttpClient` lee la cookie `XSRF-TOKEN` y envía la cabecera `X-XSRF-TOKEN` en POST, PUT, PATCH y DELETE con **URL relativa**. Son los mismos nombres que usa Laravel, así que no hay que configurar nada.
- **`proxy.conf.json`:** `ng serve` reenvía `/api`, `/login`, `/logout` y `/sanctum` a `localhost:8000`. El navegador cree que todo es el mismo origen (`localhost:4200`): sin CORS y con la cookie de sesión viajando sola.

**Prompt para Claude Code:**

```text
Fase 6 de docs/SPEC.md. Crea la app Angular 22 en frontend/ (ng new, SCSS, routing, sin SSR).
1. Bootstrap por npm; src/styles.scss importa ../design/_tokens.scss ANTES de bootstrap
   (stylePreprocessorOptions.includePaths si hace falta). Mismos colores que la parte Blade.
2. proxy.conf.json para /api, /login, /logout y /sanctum hacia http://localhost:8000; script npm start que lo use.
3. core/: AuthService (signal currentUser, me(), logout()), authGuard funcional, interceptor funcional
   para 401/419, environment con loginUrl y backendUrl.
4. Shell: navbar Bootstrap con Presupuestos, Clientes, Referencias, nombre de usuario y Salir.
   Rutas lazy con loadComponent: /quotes, /quotes/new, /quotes/:id, /customers, /references; ruta por defecto /quotes.
5. Estructura por features: core/, shared/, features/quotes, features/customers, features/references.
Commits separados. Explícame app.config.ts, la diferencia entre guard e interceptor, y cómo viaja
la cookie de sesión de Laravel hasta una petición de Angular a través del proxy.
```

**Verificación:**

- Con Laravel arrancado, `npm start` en `frontend/` y abre `localhost:4200`. Sin sesión te lleva al login; tras hacer login vuelves a la SPA con tu nombre en la navbar.
- DevTools → Network: la petición `/api/user` lleva la cookie de sesión y responde 200.
- Salir → vuelves a la landing y `/api/user` ya da 401.

**Commits:**

- `chore(frontend): scaffold Angular 22 application`
- `style(frontend): integrate Bootstrap with shared design tokens`
- `feat(frontend): add dev proxy, auth service, guard and interceptor`
- `feat(frontend): add app shell with navigation and lazy routes`

**Preguntas que te pueden hacer:**

- ¿Por qué cookie y no un token en `localStorage`? → La cookie de sesión es `HttpOnly`: un XSS no puede leerla. El CSRF lo cubre el par cookie y cabecera `XSRF-TOKEN`.
- ¿Signals o RxJS? → Signals para el estado de la vista; RxJS sigue siendo lo natural para HTTP y flujos asíncronos. `toSignal()` los conecta.

## Fase 7 · Angular: gestión de presupuestos

**Objetivo:** las pantallas de negocio. Listado de presupuestos con filtros, editor con líneas dinámicas e IVA por línea, cambio de estado, y clientes y referencias (listado y alta). Tiempo estimado: 5 h. Es la fase más larga; si vas justo, recorta clientes y referencias a solo listado.

**Qué debes entender:**

- **Servicio de datos por recurso** (`QuotesApi`, `CustomersApi`...): encapsula `HttpClient` y los tipos. Los componentes nunca llaman a `http` directamente.
- **Interfaces TypeScript** que reflejan el JSON de la API (`Quote`, `QuoteLine`, `QuoteTotals`). Son el contrato con el backend en el lado del cliente.
- **Smart vs presentational:** la página (*smart*) carga datos y gestiona el estado; componentes pequeños como `status-badge` o `totals-summary` solo reciben `input()` y emiten `output()`.
- **Reactive Forms con `FormArray`:** el formulario del presupuesto tiene un `FormArray` de líneas; cada línea es un `FormGroup`. Añadir o quitar líneas es hacer `push` o `removeAt`. Validadores: `required`, `min(0)`...
- **Previsualización de totales:** un `computed()` (o `valueChanges`) recalcula en el cliente mientras escribes. Tras guardar, se muestran los totales que devuelve la API, que son los buenos.
- **Errores 422 del backend:** se mapean a los campos del formulario para que el usuario vea el mensaje en su sitio.
- **Pipes:** `currency:'EUR':'symbol':'1.2-2':'es'` y `date` con `LOCALE_ID` en `es` para ver 1.234,56 €.

**Prompt para Claude Code:**

```text
Fase 7 de docs/SPEC.md. Implementa features/ de Angular contra la API de la Fase 4.
1. models/ con interfaces que reflejen exactamente los Resources de Laravel; servicios *Api por recurso.
2. Listado de presupuestos: tabla Bootstrap responsive con número, cliente, fecha, estado (badge por color)
   y total; filtros por estado y cliente; paginación; botón Nuevo. Estados de carga, vacío y error.
3. Editor (/quotes/new y /quotes/:id): cabecera (cliente con buscador, fechas, notas) + FormArray de líneas.
   Al elegir referencia se rellenan descripción, precio e IVA (editables). Selector de tipo de IVA por línea.
   Panel de totales con desglose por IVA (previsualización), mapeo de errores 422 a los campos,
   modo solo lectura si el estado no es draft.
4. Acciones de estado (Enviar, Aceptar, Rechazar, Reabrir) según el estado actual, Borrar solo en draft,
   e Imprimir que abre {environment.backendUrl}/quotes/:id/print (URL absoluta a Laravel: una relativa
   la capturaría el router de Angular) en otra pestaña.
5. Clientes y referencias: listado con búsqueda y formulario de alta.
6. LOCALE_ID es, pipes de moneda y fecha. Un test unitario del cálculo de previsualización.
Ve pantalla a pantalla con un commit por pantalla. En el editor, para y explícame el FormArray y cómo fluye
un cambio de cantidad hasta el total que se ve.
```

**Verificación:**

- Crea un presupuesto con 3 líneas y dos tipos de IVA. El desglose de la previsualización coincide con el que devuelve la API tras guardar.
- Fuerza un error (cantidad 0): el mensaje del backend aparece bajo el campo.
- Envía el presupuesto: queda en solo lectura y desaparece Borrar. Imprimir abre la vista Blade.
- Recarga la página en `/quotes/5`: el guard deja pasar y la pantalla se reconstruye desde la API.

**Commits** (uno por pantalla):

- `feat(frontend): add API models and data services`
- `feat(frontend): add quotes list with filters and pagination`
- `feat(frontend): add quote editor with dynamic lines and per-line VAT`
- `feat(frontend): add quote status actions and print link`
- `feat(frontend): add customers and product references screens`

**Preguntas que te pueden hacer:**

- ¿Por qué Reactive Forms y no template-driven? → Formulario dinámico (líneas que se añaden y quitan), validación programática y tipado. Con FormArray es lo natural.
- Si la previsualización y la API no cuadran, ¿quién gana? → La API. Por eso la previsualización es solo orientativa y, tras guardar, se pinta lo que devuelve el servidor.

## Fase 8 · Entrega: README, capturas y declaración de IA

**Objetivo:** que el evaluador clone, instale y entre en menos de 5 minutos, y que la documentación cuente tus decisiones. Tiempo estimado: 2 h.

**Opcional, muy recomendable — un solo servidor:** `ng build` con `outputPath` hacia `backend/public/app` y `baseHref` `/app/`, más la ruta Laravel `/app/{any?}` (con `auth`) que devuelve su `index.html`. Así el evaluador solo necesita `php artisan serve`, y `FRONTEND_URL` pasa a ser `http://localhost:8000/app`. Explícalo en el README como «modo demo» frente a «modo desarrollo».

**README.md** (en español, con estas secciones):

1. Qué es: una frase y una captura principal.
2. Stack y versiones exactas.
3. Arquitectura: el diagrama de esta spec y por qué Blade + SPA.
4. Requisitos: PHP 8.3+ con `pdo_sqlite`, Composer, Node LTS.
5. Instalación paso a paso, copiable: `composer install`, `cp .env.example .env`, `php artisan key:generate`, `touch database/database.sqlite`, `php artisan migrate --seed`, `npm install && npm run build` (en backend y frontend).
6. Arranque (modo desarrollo y modo demo) y credenciales demo.
7. Tests: cómo lanzarlos y qué cubren.
8. Decisiones técnicas: snapshots, decimal, totales en backend, Sanctum por cookie, SQLite, alcance excluido.
9. Capturas: landing, login, listado, editor con desglose de IVA, vista imprimible, móvil.
10. Uso de IA: enlace a `docs/AI_USAGE.md`.

**`docs/AI_USAGE.md` (declaración de uso de IA):**

- **Herramientas:** Claude (chat) para diseñar la arquitectura y el modelo de datos y preparar esta especificación; Claude Code para implementar fase a fase; Laravel Boost y el MCP de Angular CLI para dar al agente documentación de las versiones exactas.
- **Para qué sí y para qué no:** la IA generó código y tests. Las decisiones de dominio (IVA por línea con snapshot, estados, totales en backend, Blade para la parte pública y el login) son tuyas y se revisaron diff a diff.
- **Criterio:** qué corregiste o rechazaste (anota ejemplos reales mientras trabajas: una migración con `float`, un cálculo en el controller, un import obsoleto...).
- **2-3 prompts representativos**, copiados de esta spec: el de la Fase 3 (tests primero), el de la Fase 4 (API) y el de la Fase 7 (editor con FormArray).

**Capturas:** a 1440 px y a 390 px (móvil) desde DevTools, guardadas en `docs/screenshots/` con nombres descriptivos.

**Revisión final del historial:** `git log --oneline` debe leerse como una historia (scaffold → modelo → lógica → API → web → SPA → docs). Sin commits «fix», «wip» o «cambios».

**Prueba de humo final:** clona el repo en otra carpeta, sigue el README al pie de la letra y apunta cada paso que falle. Es exactamente lo que hará el evaluador.

**Commits:**

- `feat(repo): serve Angular build from Laravel in demo mode`
- `docs(repo): write README with setup, architecture and decisions`
- `docs(repo): add screenshots and AI usage statement`

## Ensayo de cambios en directo

En la revisión te pedirán cambios pequeños en directo. Antes de tocar el teclado, **di en voz alta qué capas vas a tocar**: eso es lo que evalúan. Practica cada cambio en una rama, cronométrate y luego descarta la rama (`git switch main && git branch -D ensayo-x`).

| Cambio que te pueden pedir | Capas y ficheros que toca | Objetivo |
| --- | --- | --- |
| Añadir «forma de pago» al presupuesto | Migración nueva → `Quote::$fillable` → FormRequest → `QuoteResource` → interfaz TS → editor → vista print | 15 min |
| Cambiar el color corporativo | `design/_tokens.scss` (una variable) → recompilar Vite y Angular | 2 min |
| Nuevo estado `expired` (caducado) | `QuoteStatus` (caso, `label()`, transiciones) → test → badge en Angular | 10 min |
| Filtrar presupuestos por rango de fechas | `QuotesController@index` (query) → `QuotesApi` → filtros del listado | 15 min |
| Columna «Fecha de validez» en el listado | Solo Angular (el dato ya viene en el Resource) | 3 min |
| Prohibir descuentos de más del 50 % | Regla en el FormRequest (`lines.*.discount_pct`) → test → validador `max(50)` en el FormArray | 8 min |
| Formulario de contacto que guarde en BD | Migración `contact_messages` → modelo → FormRequest → controller web → Blade con `@csrf` y `@error` | 20 min |
| Duplicar un presupuesto | Endpoint `POST /api/quotes/{id}/duplicate` (nuevo número, estado `draft`, copia de líneas) → botón en Angular | 20 min |
| Añadir un tipo de IVA nuevo | Solo datos: seeder o inserción en `vat_types`. No hay que tocar código: el diseño lo permite | 2 min |

Cómo hacerlo con Claude Code durante la revisión, en voz alta:

1. «Esto toca la migración, el modelo, la validación, el Resource y dos pantallas.»
2. Pide a Claude Code un plan antes del código (el `CLAUDE.md` ya lo obliga). Revisa el plan con el entrevistador.
3. Ejecuta, lanza los tests, enséñalo en el navegador y haz un commit con mensaje convencional.

Si algo se rompe: lee el error en voz alta y razona por capas. Los sospechosos habituales son el 419 (CSRF o sesión caducada), el 401 (`SANCTUM_STATEFUL_DOMAINS` o proxy), el 422 (mira `errors` en la respuesta) y una migración pendiente (`php artisan migrate:status`).

## Chuleta de preguntas del entrevistador

Respuestas cortas para tener a mano. Las preguntas específicas de cada fase están en su sección.

**Arquitectura**

- *¿Por qué Blade y Angular a la vez?* → Lo pide el enunciado, pero cada uno tiene su papel: Blade para contenido público, indexable y el login; Angular para la herramienta interactiva. Comparten modelo de datos y tokens de diseño.
- *¿Qué cambiarías para producción?* → MySQL o PostgreSQL, HTTPS con `SESSION_SECURE_COOKIE`, colas para emails, caché de configuración y rutas, build de Angular servido por Nginx o por el propio Laravel, y CI con los tests.
- *¿Cómo escalaría?* → La API es stateless salvo la sesión: sesiones en Redis y varios servidores detrás de un balanceador.

**Laravel**

- *Eloquent vs Query Builder* → Eloquent para el dominio (relaciones, casts); Query Builder para informes o consultas pesadas.
- *¿Qué es el Service Container?* → El inyector de dependencias de Laravel: construye las clases que pides y sus dependencias.
- *Middleware* → Capas que envuelven la petición: sesión, CSRF, `auth`, rate limit.
- *Policy vs Gate* → Las dos autorizan; la Policy agrupa las reglas de un modelo.

**Angular**

- *Ciclo de vida* → `ngOnInit` para cargar datos; con signals se usa menos, porque `computed` y `effect` sustituyen mucho código de ciclo de vida.
- *`input()` / `output()`* → API basada en signals que sustituye a `@Input` y `@Output`.
- *Control flow* → `@if`, `@for` (con `track` obligatorio) y `@switch` sustituyen a `*ngIf` y `*ngFor`.
- *Lazy loading* → `loadComponent` en las rutas: menos JavaScript en la carga inicial.

**Seguridad**

- XSS: Blade y Angular escapan por defecto. CSRF: token en formularios Blade y cookie `XSRF-TOKEN` en la SPA. Mass assignment: `$fillable`. SQL injection: Eloquent usa bindings. Fuerza bruta: rate limit en el login.

**Uso de IA**

- *¿Qué parte es tuya?* → La especificación: dominio, modelo de datos, reglas, arquitectura y orden de las fases. La IA implementa y yo reviso cada diff, como haría con un desarrollador junior muy rápido.
- *¿Cómo evitas que la IA use APIs obsoletas?* → `CLAUDE.md` con convenciones, Laravel Boost y el MCP de Angular con documentación de la versión exacta, tests y revisión humana.

**Fuentes de versiones** (consultadas el 17/11/2026): [Laravel 13 release notes](https://laravel.com/docs/13.x/releases) · [Angular: ciclo de soporte](https://endoflife.date/angular) · [Angular 23 en junio de 2027](https://angular.love/angular-new-release-cycle-release-schedule-change-explained)
