# Declaració d'ús de la IA

## Eines

| Eina | Per a què |
| --- | --- |
| **Claude Code** (aplicació d'escriptori, model Claude Opus 5.5) | Tutor i company de programació: planificar fase a fase, generar codi, tests i documentació, i explicar cada decisió. |
| **Laravel Boost** (servidor MCP i guies) | Donar a la IA les convencions de Laravel 13 i accés a la documentació de la versió instal·lada. |
| **Claude in Chrome** | Extreure la paleta i la tipografia reals de softneos.com i suite.softneos.com, i fer les captures del README. |

## Com s'ha treballat

- **Primer, l'especificació.** `docs/SPEC.md` defineix les fases, el model de dades, les regles i el contracte de l'API. `CLAUDE.md` obliga la IA a resumir el pla i esperar el meu vistiplau abans d'escriure codi, a explicar els conceptes i a proposar cada commit.
- **Parts escrites per mi** per entendre el nucli: `QuoteStatus::canTransitionTo()`, l'arrodoniment *half up* de `QuoteCalculator`, `QuoteService::buildLine()` (regla de còpia o *snapshot*) i `LoginController::store()`. La IA va revisar cadascuna i en va assenyalar els errors (per exemple, no copiar `vat_type_id` de la referència o un valor per defecte d'IVA del 0 % que amagaria errors).
- **Commits sense coautoria de la IA:** l'ús de la IA es declara aquí, no a cada commit.

## Criteri: errors de la IA detectats i corregits

- Va afirmar que `constrained()` crea índexs a les claus foranes; a SQLite no és així. Es van afegir `->index()` explícits abans del commit.
- Va declarar `Response` com a tipus de retorn dels `store()` quan retornen `JsonResponse`: els tests de funcionalitat ho van detectar (error 500) i hi ha un commit `fix` específic.
- Va anomenar `new()` un estat de factory, que xoca amb `Factory::new()`.
- Va registrar dues rutes amb el mateix nom (`quotes.index` a la web i a l'API) i l'inici de sessió redirigia a `/api/quotes`. Es va detectar provant-ho al navegador i es va corregir amb el prefix `api.` i un test que compara l'URL literal.
- **Abast:** la primera versió de la web copiava eslògans i productes reals de SoftNeos, i l'especificació havia interpretat Angular com una SPA separada. En contrastar-ho amb l'enunciat literal es va corregir: textos propis, contingut públic des de la base de dades i Angular dins de vistes Blade. Està documentat a `docs/SPEC.md` («Correcciones de alcance»).

## Prompts representatius

Els prompts es van escriure en castellà; aquí en tens la traducció.

1. **Inici guiat:**
   > «Revisa l'estructura del projecte. Dins de la carpeta docs tens l'especificació (docs/SPEC.md). En aquesta especificació hi ha tota la feina que has de fer, i al CLAUDE.md, com l'has de fer. Així que guia'm en l'execució d'aquest projecte per a la prova tècnica.»

2. **Revisió crítica de l'abast:**
   > «Tornem a llegir amb atenció la proposta de prova que em va donar el responsable tècnic de SoftNeos i avaluem si el que hem fet és realment el que ens demana. Perquè em fa l'efecte que has intentat fer una rèplica de la web, i no és això el que t'he demanat.»

3. **Decisió d'arquitectura:**
   > «Jo crec que el que ens demana l'enunciat és que el motor de plantilles sigui Blade de Laravel, però que hi hagi vistes que funcionin amb Angular. Això és possible?»
   >
   > (Resposta: tres opcions amb pros i contres; es va triar Angular Elements dins de Blade.)
