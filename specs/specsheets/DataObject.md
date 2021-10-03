# Datenobjekt-Spezifikation (DataObject)
## Was ist ein Datenobjekt?
- Ein Datenobjekt ist eine Sammlung von Eigenschaften eines realen Objektes -
Jedes einzelne reale Objekt, das in einer Octopus-Installation gespeichert ist, ist ein Datenobjekt.
Das kann z.B. ein Blogartikel, ein Foto, eine Person oder fast jeder andere Gegenstand sein.
Ebenso wie die Objekte der echten Welt Eigenschaften besitzen (eine Person hat einen Namen, ein
Geburtsdatum usw.), besitzen auch ihre digitalen Entsprechungen Eigenschaften.
Die abstrakte Klasse DataObject stellt Methoden zur Erstellung, Bearbeitung, Ausgabe und Löschung
von Datenobjekten bereit. Über diese läuft auch die Datenbankkommunikation.

## Eigenschaften
- `new` - **private**, *bool*: true, wenn das Objekt noch nicht in der Datenbank gespeichert ist, z.B. nach der Erzeugung durch `create()`.
- `empty` - **private**, *bool*: true, wenn das Objekt keine Daten enthält, z.B. direkt nach der Initialisierung.
- `frozen` - **private**, *bool*: true, wenn der Datenbankzugang durch `freeze()` unterbunden wurde. Siehe *Funktionen/freeze()*.
- `related_objects` - **private**, *array*: Enthält Listen von Objekten, die über Relationen mit dem Objekt verbunden sind.
- `id` - **DB**, *string(8), base64*: zufällig generiert bei Erzeugung, nicht veränderbar.
- `longid` - **DB**, *string(9-128), a-z0-9-*: festgelegt beim ersten Import, nicht veränderbar.

## Constants


## Functions
### `__construct()` – Erstelle eine neue Instanz der Klasse DataObjekt
Stadium: 1 – Konstruktion  

### `create()` - Erzeuge ein leeres Datenobjekt
Stadium: 2 – Initialisierung  

### `pull()` - Lade ein vorhandenes Datenobjekt aus der Datenbank
Stadium: 2 – Beladung  

### `load()` - Lade Datenbank-Daten in das Datenobjekt hinein
Stadium: 2 – Beladung  

### `push()` - Speichere das Datenobjekt in der Datenbank
Stadium: 4 – Speicherung  

### `delete()` - Lösche das Datenobjekt aus der Datenbank
Stadium: 4 – Löschung  

### `edit()` - Importiere Formulardaten in das Datenobjekt
Stadium: 3 – Bearbeitung  

### `freeze()` - Verhindere weitere Bearbeitung des Datenobjekts (einfrieren)
Stadium: 5 – Ausgabe  

### `arrayify()` - Erstelle ein Array-Abbild des Datenobjekts (z.B. zur API-Ausgabe)
Stadium: 5 – Ausgabe  


//---------------------
STADIEN:
1. Construction – Konstruktion
2. Initialization / Loading – Initialisierung / Beladung
3. Editing – Bearbeitung
4. Storing / Deleting – Speicherung / Löschung
5. Output – Ausgabe
6. Destruction – Destruktion
