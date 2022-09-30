<dialog class="entity-picker grid" data-entity="image">
	<form method="dialog">
		<h2>Bild auswählen</h2>
		<button type="button" class="close-x" data-action="cancel">Abbrechen</button>
		<div class="pagination">
			<div class="infotext"></div>
			<div class="items" data-items="first -10 -3 -2 -1 current +1 +2 +3 +10 last"></div>
			<template data-for="infotext">
				Seite {pulledPage} von {totalPagesAvailable}.<br>
				Angezeigt werden Bilder {firstDisplayedNumber} bis {lastDisplayedNumber}
				von insgesamt {totalEntitiesAvailable} Bildern.
			</template>
			<template data-for="item">
				<button type="button" data-page="{relpage}">{abspage}</button>
			</template>
			<template data-for="item-first">
				<button type="button" data-page="first">Erste</button>
			</template>
			<template data-for="item-last">
				<button type="button" data-page="last">Letzte</button>
			</template>
		</div>
		<div class="entities">

			<template>
				<label>
					<img data-nopreload-src="{src}" data-nopreload-srcset="{srcset}">
					<input type="radio" name="result" value="{id}">
				</label>
			</template>
		</div>
		<button type="submit" data-action="submit">Ausgewähltes Bild übernehmen</button>

	</form>
</dialog>
