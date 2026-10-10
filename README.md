# LobbyInfo

Señalética digital para lobbies, como plugin de WordPress. Pensado para mostrar anuncios, avisos y contenido en las TVs de una iglesia, una oficina, una recepción o cualquier lugar con pantallas. Cada pantalla es solo una URL del sitio abierta en el navegador de la TV.

## Panel de gestión

Las personas que administran las pantallas no entran a WordPress: usan un panel propio en **`/gestor/`** (por ejemplo `https://tu-sitio/gestor/`), con su propio acceso, su marca y su diseño. Desde ahí gestionan contenidos, listas, pantallas y ajustes sin ver nada de WordPress. Entran con un usuario de WordPress que tenga permiso de edición.

## Cómo funciona

1. **Categorías:** agrupan tus contenidos como quieras (Anuncios, Eventos, Bienvenida…).
2. **Contenidos:** imagen, video, texto, YouTube o página web, con duración y vigencia opcional (desde/hasta).
3. **Listas de reproducción:** contenidos elegidos en orden, más categorías completas que se agregan solas.
4. **Pantallas:** cada una tiene una lista por defecto, una transición y reglas de programación (por ejemplo, domingos de 10:00 a 13:00 se muestra la lista "Culto").

La TV abre `https://tu-sitio/pantalla/{nombre}/`. El reproductor consulta cada 30 segundos si hubo cambios y los aplica sin recargar. Si se corta internet, sigue mostrando lo último que recibió. En el listado de pantallas se ve cuáles están en línea.

## Requisitos

- WordPress 6.4 o superior
- PHP 8.0 o superior

## Instalación

Copia la carpeta en `wp-content/plugins/lobbyinfo`, actívalo y ve a **LobbyInfo** en el menú del admin. Si `/pantalla/...` da error 404, guarda una vez **Ajustes → Enlaces permanentes**.

## Estructura (Screaming Architecture)

El código se organiza por lo que hace el sistema, no por capas técnicas:

```
src/
├── Screens/      Pantallas: tipo, programación horaria, editor, listado y estado de conexión
├── Playlists/    Listas de reproducción: tipo, resolución de contenidos y editor
├── Contents/     Contenidos: tipos, vigencia, editor y listado
├── Categories/   Categorías de contenidos
├── Player/       Reproductor público: ruta /pantalla/, API REST, vista y JS
├── Panel/        Panel de gestión de marca blanca en /gestor/ (fuera de wp-admin)
├── Settings/     Ajustes globales (marca, logo, intervalo, fondo)
├── Security/     Endurecimiento (bloquea enumeración de usuarios por REST)
└── Shared/       Utilidades comunes del administrador
```

Cada carpeta tiene sus propias clases y sus `assets/`. Las clases usan el espacio de nombres `LobbyInfo\{Área}` y se cargan automáticamente (PSR-4) desde `src/`.

## API

`GET /wp-json/lobbyinfo/v1/screens/{slug}` devuelve la lista que corresponde ahora a la pantalla, con sus contenidos y un `hash` para detectar cambios.

## Licencia

GPL-2.0-or-later. Autor: [alfcl](https://alf.cl).
