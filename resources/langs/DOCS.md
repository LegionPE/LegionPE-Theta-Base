Documentation
===
## Vars
Vars are in the format of `%var_name%`. Vars that do not exist will be sent to players literally. For line breaks, use `\n`.
## Precompiled format vars
There exists a list of precompiled format vars:

| Name | Value |
| ---: | :---- |
| wait | TextFormat::RED + "… " |
| success | TextFormat::GREEN |
| info | TextFormat::WHITE |
| symbol | TextFormat::GRAY |
| verbose | "》 " + verbosemid |
| verbosemid | TextFormat::GRAY |
| error | TextFormat::DARK_RED |
| warning | TextFormat::YELLOW |
| notify | TextFormat::LIGHT_PURPLE |
| notify2 | TextFormat::GOLD |
| em | TextFormat::AQUA |
| em1 | TextFormat::AQUA |
| em2 | TextFormat::BLUE |
| em3 | TextFormat::DARK_BLUE |
| reset | TextFormat::RESET |
| bold | TextFormat::BOLD |
| italic | TextFormat::ITALIC |

They will be replaced into format modifiers upon plugin load.
