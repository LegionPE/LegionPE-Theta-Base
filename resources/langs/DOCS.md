Documentation
===
## Vars
Vars are in the format of `%var_name%`. Vars that do not exist will be sent to players literally. For line breaks, use `\n`.
## Precompiled format vars
There exists a list of precompiled format vars:

| Name | Value |
| ---: | :---- |
| VAR_wait | TextFormat::RED + "… " |
| VAR_success | TextFormat::DARK_GREEN |
| VAR_info | TextFormat::WHITE |
| VAR_symbol | TextFormat::GRAY |
| VAR_verbose | "》 " + VAR_verbosemid |
| VAR_verbosemid | TextFormat::GRAY |
| VAR_error | TextFormat::DARK_RED |
| VAR_warning | TextFormat::YELLOW |
| VAR_notify | TextFormat::LIGHT_PURPLE |
| VAR_notify2 | TextFormat::GOLD |
| VAR_em | TextFormat::AQUA |
| VAR_em1 | TextFormat::AQUA |
| VAR_em2 | TextFormat::BLUE |
| VAR_em3 | TextFormat::DARK_BLUE |
| VAR_reset | TextFormat::RESET |
| VAR_bold | TextFormat::BOLD |
| VAR_italic | TextFormat::ITALIC |

They will be replaced into format modifiers upon plugin load.
