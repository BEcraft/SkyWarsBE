## Total Views
 | [![HitCount](http://hits.dwyl.io/BEcraft/SkyWarsBE.svg)](http://hits.dwyl.io/BEcraft/SkyWarsBE) |
 | :---: |

# English

<details><summary> How to use? </summary>

## Commands
<details><summary> Show </summary>

### `/skywars`


**Commands while you are `not` in any session:**


| Command | Description | Permission | Parameters |
| :---: | :---: | :---: | :---: |
| arena | Create a new skywars arena | skywars.partida | `<maximum players count>` `<minimum players count>` `<solo = 0, duos = 1>` |
| create | Start a new creator session | skywars.mapa | None |
| lobby | Set the waiting lobby | op | None |
| help | Check skywars information | None | None |

<br/>

**Commands while you are creating a map:**

| Command | Description | Parameters |
| :---: | :---: | :---: |
| map | Select the target map | None |
| maximum | Choose maximun slot | `<maximum slots count>` |
| add | Add a new spawn point | None
| remove | Remove the last added point | None |
| rall | Reset all the options | None
| progress | Check your progress | None
| save | Save map | None |

<br/>

**Commands while you are playing:**

| Command | Description | Params |
| :---: | :---: | :---: |
| leave | Leave the game | None |

</details>

## API
<details><summary> Show </summary>

### - Events
**Importing classes:**

```php

use SkyWars\Eventos\{
	SalirEvento as LeaveEvent,
	UnirseEvento as JoinEvent,
	GanarEvento as WinEvent,
	AsesinarEvento as KillEvent,
	RequerirNickEvento as NickRequestEvent,
	SkyWarsEvento as SkyWarsEvent
};

```

### Events that are possible to cancel:
  - `JoinEvent`

### How can i get the player by using these events?

**You can get the player by using: `SkyWarsEvent::getPlayer()`, example:**

```php

function join(JoinEvent $event){
	$player = $event->getPlayer();
}

```

### Functions:

- **LeaveEvent**

| Function | Description | Return |
| :---: | :---: | :---: |
| `getKills()` | Get the player's kills count | `Integer` |

<br/>

- **JoinEvent**

| Function | Description | Return |
| :---: | :---: | :---: |
| `getGame()` | Get the game's identificator where player is trying to join | `String` |

<br/>

- **WinEvent**

| Function | Description | Return |
| :---: | :---: | :---: |
| `getKills()` | Get the player's kills count | `Integer` |

<br/>

- **KillEvent**

| Function | Description | Return |
| :---: | :---: | :---: |
| `getVictim()` | Get the victim | `Player` |

<br/>

- **NickRequestEvent**

| Function | Description | Return |
| :---: | :---: | :---: |
| `setNick(string)` | Set a new nickname | `void` |
| `getNick()` | Get the current player's nickname | `string` |

</details>

### Tips

**1. You can fill a chest up by touching it with a `Diamond` or `Iron Ingot` while in creator mode.**

**Different types of items:**
- `Diamond:`
  - _Maximum items_
- `Iron Ingot`
  - _Medium items_

<br/>

**2. You can add a "join" sign by typing`[skywarsbe]`, example:**

<details><summary> Show </summary>

![](https://i.imgur.com/RKimpnE_d.webp?maxwidth=640&shape=thumb&fidelity=medium)

</details>

</details>

# Español

<details><summary> ¿Como usar? </summary>

## Comandos
<details><summary> Mostrar </summary>

### `/skywars`

**Comandos mientras `no` estas en alguna sección:**

| Comando | Descripción | Permiso | Parámetros |
| :---: | :---: | :---: | :---: |
| arena | Crea una nueva partida de skywars | skywars.partida | `<máximo de jugadores>` `<mínimo de jugadores>` `<solo = 0, duos = 1>` |
| crear | Comienza una nueva sección de creador | skywars.mapa | Ninguno |
| lobby | Asigna el lugar de espera | op | Ninguno |
| ayuda | Revisa la información del SkyWars | Ninguno | Ninguno |

<br/>

**Comandos mientras estas creando un mapa:**

| Comando | Descripción | Parámetros |
| :---: | :---: | :---: |
| mapa | Selecciona el mapa | Ninguno |
| máximo | Selecciona la cantidad máxima de posiciones | `<cantidad máxima>` |
| agregar | Agrega una nueva posición | Ninguno |
| remover | Elimina la ultima posición | Ninguno |
| rtodo | Reiniciar todas las opciones | Ninguno |
| progreso | Revisa tu progreso | Ninguno |
| guardar | Guarda el mapa | Ninguno |

<br/>

**Comandos mientras estas jugando:**

| Comando | Descripción | Parámetros |
| :---: | :---: | :---: |
| salir | Salir de la partida | Ninguno |

</details>

## API
<details><summary> Mostrar </summary>

### - Eventos
**Importar clases:**

```php

use SkyWars\Eventos\{
	SalirEvento,
	UnirseEvento,
	GanarEvento,
	AsesinarEvento,
	RequerirNickEvento,
	SkyWarsEvento
};

```


### Eventos que se pueden cancelar:
  - `UnirseEvento`


### ¿Como puedo conseguir al jugador usando estos eventos?

**Puedes conseguir al jugador mediante; `SkyWarsEvento::conseguirJugador()`, ejemplo:**

```php

function entrar(UnirseEvento $evento){
	$jugador = $evento->conseguirJugador();
}

```


### Funciones:

- **SalirEvento**

| Función | Descripción | Retorno |
| :---: | :---: | :---: |
| `conseguirAsesinatos()` | Consigue la cantidad de asesinatos que ha cometido el jugador | `Entero` |

<br/>

- **UnirseEvento**

| Función | Descripción | Retorno |
| :---: | :---: | :---: |
| `conseguirPartida()` | Consigue el identificador de la partida a la que el jugador está tratando unirse | `Cadena de texto` |

<br/>

- **GanarEvento**

| Función | Descripción | Retorno |
| :---: | :---: | :---: |
| `conseguirAsesinatos()` | Consigue la cantidad de asesinatos que ha cometido el jugador | `Entero` |

<br/>

- **AsesinarEvento**

| Función | Descripción | Retorno |
| :---: | :---: | :---: |
| `conseguirVictima()` | Consigue la victima | `Player` |

<br/>

- **RequerirNickEvento**

| Función | Descripción | Retorno |
| :---: | :---: | :---: |
| `asignarNick(cadena de texto)` | Asigna un sobrenombre del jugador que ha entrado a la partida | `vacío` |
| `conseguirNick()` | Conseguir el sobrenombre del jugador | `cadena de texto` |

</details>

### Consejos

**1. Puedes tocar algún cofre con un `Diamante` ó `Lingote de hierro` para agregar diferentes tipos de objetos mientras estas en el modo creador**

**Tipos de objetos:**
- `Diamante:`
  - _Objetos maxímo_
  
- `Lingote de hierro:`
  - _Objetos medianos_

<br/>

**2. Puedes agregar un cartel para entrar escribiendo `[skywarsbe]`, ejemplo:**

<details><summary> Mostrar </summary>

![](https://i.imgur.com/RKimpnE_d.webp?maxwidth=640&shape=thumb&fidelity=medium)

</details>

</details>

<br/>

**Twitter:** [Click](https://twitter.com/BEcraft_MCPE)

**YouTube:** [Click](https://m.youtube.com/channel/UCPdNfy0EYn-q8Kl4vnX1pRw)