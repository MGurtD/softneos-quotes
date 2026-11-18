# Declaración de uso de IA

## Herramientas

| Herramienta | Para qué |
| --- | --- |
| **Claude Code** (app de escritorio, modelo Claude Opus 5.5) | Tutor y par de programación: planificar fase a fase, generar código, tests y documentación, y explicar cada decisión. |
| **Laravel Boost** (servidor MCP y guías) | Dar a la IA las convenciones de Laravel 13 y acceso a la documentación de la versión instalada. |
| **Claude in Chrome** | Extraer la paleta y la tipografía reales de softneos.com y suite.softneos.com, y tomar las capturas del README. |

## Cómo se ha trabajado

- **Especificación primero.** `docs/SPEC.md` define fases, modelo de datos, reglas y contrato de la API. `CLAUDE.md` obliga a la IA a resumir el plan y esperar mi OK antes de escribir código, a explicar los conceptos y a proponer cada commit.
- **Partes escritas por mí** para entender el núcleo: `QuoteStatus::canTransitionTo()`, el redondeo *half up* de `QuoteCalculator`, `QuoteService::buildLine()` (regla de snapshot) y `LoginController::store()`. La IA revisó cada una y señaló los fallos (por ejemplo, no copiar `vat_type_id` de la referencia o un valor por defecto de IVA 0 % que ocultaría errores).
- **Commits sin coautoría de IA:** el uso de IA se declara aquí, no en cada commit.

## Criterio: errores de la IA detectados y corregidos

- Afirmó que `constrained()` crea índices en las claves foráneas; en SQLite no es así. Se añadieron `->index()` explícitos antes del commit.
- Declaró `Response` como tipo de retorno de los `store()` cuando devuelven `JsonResponse`: los tests de feature lo detectaron (500) y hay un commit `fix` específico.
- Llamó `new()` a un estado de factory, que choca con `Factory::new()`.
- Registró dos rutas con el mismo nombre (`quotes.index` en web y en la API); el login redirigía a `/api/quotes`. Se detectó probando en el navegador y se corrigió con el prefijo `api.` y un test que compara la URL literal.
- **Alcance:** la primera versión de la web copiaba eslóganes y productos reales de SoftNeos, y la SPEC había interpretado Angular como una SPA separada. Al contrastar con el enunciado literal se corrigió: textos propios, contenido público desde base de datos y Angular dentro de vistas Blade. Está documentado en `docs/SPEC.md` («Correcciones de alcance»).

## Prompts representativos

1. **Arranque guiado:**
   > «Revisa la estructura del proyecto. Dentro de la carpeta docs tienes la especificación (docs/SPEC.md). En esta especificación tienes todo el trabajo que debes realizar y en el CLAUDE.md el cómo lo debes realizar. Así que guíame en la ejecución de ese proyecto para la prueba técnica.»

2. **Revisión crítica del alcance:**
   > «Vamos a volver a leer detenidamente la propuesta de prueba que me dio el responsable técnico de SoftNeos y evaluamos si realmente lo que hemos hecho es realmente lo que nos pide. Porque a mí me da que has intentado hacer una réplica de la web cuando realmente yo no es lo que te he pedido.»

3. **Decisión de arquitectura:**
   > «Yo creo que lo que nos pide el enunciado es que el motor de plantillas sea Blade de Laravel, pero que haya vistas que funcionen con Angular. ¿Esto es posible?»
   >
   > (Respuesta: tres opciones con pros y contras; se eligió Angular Elements dentro de Blade.)
