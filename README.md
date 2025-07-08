# SimpleReport

Un plugin desarrollado con la finalidad de mantener orden y seguridad en tu servidor de minecraft bedrock.

## Características

- ✅ Sistema de reportes con comando `/report`
- ✅ Ver reportes con comando `/viewreports` 
- ✅ Resolver reportes con comando `/resolvereport`
- ✅ Notificaciones automáticas a administradores
- ✅ Sistema anti-spam con cooldown configurable
- ✅ Almacenamiento en archivos JSON
- ✅ Sistema de permisos integrado
- ✅ Validación de longitud de razones
- ✅ Paginación para visualizar reportes
- ✅ Sistema de webhooks de discord usando CortexPE

## Instalación

1. Descarga todos los archivos del plugin
2. Coloca la carpeta completa en la carpeta `plugins/` de tu servidor PocketMine-MP
3. Reinicia el servidor
4. El plugin se activará automáticamente

## Estructura de Archivos

```
SimpleReport/
├── plugin.yml
├── resources/
│   └── config.yml
└── src/
    └── madversal/
        └── simplereport/
            ├── Main.php
            ├── commands/
            │   ├── ReportCommand.php
            │   ├── ViewReportsCommand.php
            │   └── ResolveReportCommand.php
            └── managers/
                └── ReportManager.php
```

## Comandos

| Comando | Descripción | Permiso | Uso |
|---------|-------------|---------|-----|
| `/report <jugador> <razón>` | Reportar un jugador | `simplereport.use` | `/report (user) (reason)` |
| `/viewreports [página]` | Ver todos los reportes | `simplereport.viewreports` | `/viewreports 1` |
| `/resolvereport <id>` | Marcar reporte como resuelto | `simplereport.admin` | `/resolvereport 5` |

### Aliases
- `/reports` - Alias para `/viewreports`

## Permisos

| Permiso | Descripción | Por defecto |
|---------|-------------|-------------|
| `simplereport.use` | Permite usar el comando /report | Todos los jugadores |
| `simplereport.viewreports` | Permite ver reportes | Solo si tienes el permiso o eres OP |
| `simplereport.admin` | Recibe notificaciones y puede resolver reportes | Solo si tienes el permiso o eres OP |

## Configuración

El archivo `config.yml` permite editar:

- Formato de notificaciones a administradores
- Cooldown entre reportes (anti-spam)
- Longitud mínima y máxima de razones
- Reportes por página en la visualización
- Configuraciones de limpieza automática
- Tu webhook

## Ejemplo de Uso

1. **Reportar un jugador:**
   ```
   /report WhatClever Está griffeando mi casa
   /report (user) (reason)
   ```

2. **Ver reportes (permiso necesario):**
   ```
   /viewreports
   /viewreports 2
   /viewreport (page)
   ```

3. **Resolver un reporte (permiso necesario):**
   ```
   /resolvereport 1
   /resolvereport (id)
   ```

## Funcionalidades

- **Anti-spam:** Los jugadores no pueden reportar más de una vez cada 60 segundos (configurable)
- **Validación:** Las razones deben tener entre 5 y 100 caracteres
- **Notificaciones:** Todos los administradores online reciben notificaciones instantáneas
- **Data:** Los reportes se guardan en `plugin_data/SimpleReport/reports.json`
- **Cleanup:** Opción de limpieza automática de reportes antiguos resueltos
- **Webhook:** Mandar un reporte a discord si es que no hay ningun administrador conectado

## Requisitos

- PocketMine-MP 5.0.0 o superior
- PHP 8.2 o superior

## Autor

Desarrollado por **@MadVersal** ❤️
