# Cómo contribuir a MagicLobby

Gracias por aportar a la primera herramienta open source de [Kombi](https://kombi.cl).

## ¿Encontraste un problema o tienes una idea?

Todo se gestiona desde **GitHub Issues**:
<https://github.com/kombicl/MagicLobby/issues>

- 🐞 **Problema:** usa la plantilla "Reportar un problema".
- 💡 **Idea / mejora:** usa la plantilla "Proponer una mejora".

## Flujo de cambios (nadie empuja directo a `main`)

1. Crea una rama desde `main`: `git checkout -b arreglo-x`.
2. Haz tus cambios y confírmalos.
3. Abre un **Pull Request** hacia `main`.
4. El chequeo de CI (sintaxis PHP) debe pasar ✅.
5. El/la mantenedor(a) revisa y **aprueba el merge**. Recién ahí entra a `main`.

> `main` está protegida: los cambios entran solo por Pull Request aprobado.

## Publicar una nueva versión (y que llegue la actualización)

Las instalaciones de WordPress reciben el aviso de actualización cuando existe
un **Release** nuevo en GitHub. El proceso es automático:

1. En el PR, sube el número de versión en **dos lugares** de `magiclobby.php`:
   - la cabecera `Version:`
   - la constante `MAGICLOBBY_VERSION`
2. Al fusionar ese PR a `main`, el workflow **Release** crea solo el Release
   `vX.Y.Z` correspondiente.
3. En unas horas (o al forzar "Buscar actualizaciones"), cada sitio con
   MagicLobby verá la actualización en **Escritorio → Plugins** y podrá
   instalarla con un clic.

Usa [versionado semántico](https://semver.org/lang/es/): `MAYOR.MENOR.PARCHE`.

## Estilo

- PHP siguiendo el estilo del propio repositorio (sangría con tabulaciones,
  nombres y comentarios en español, `defined( 'ABSPATH' ) || exit;` arriba).
- Arquitectura *screaming*: cada dominio en `src/{Área}/`.
- Antes de subir: `php -l` a los archivos que tocaste.
