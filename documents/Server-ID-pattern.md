Server ID pattern
===
Every single server instance that runs the Legion PE upon the same MySQL database should have its unique integer ID. This document states the pattern of defining server IDs.

## Rule 1: Server class
The second least significant byte of a server ID SHOULD be the class (i.e. type ID) of the server.

## Rule 2: Server host
The third significant byte and the following more significant bytes of a server ID SHOULD be the order of the deployment of the server's host. For example, the host at `168.235.80.150` is the second deployed VPS (even though the first deployed VPS is not used as an MCPE server), so its server ID should be `0x2**`.

## Rule 3: Praticality
Production servers MUST have smaller server ID (that SHOULD be smaller than 8 at its least significant byte) than test servers at the same host (that SHOULD be graeter than or equal to 8 at its least significant byte).

## Rule 4: Server port
If multiple servers are hosted on the same machine, order servers in the ascending order of port.
