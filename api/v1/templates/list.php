{
	"status": "Octopus API v1 OK",
	"total_available": <?= $Entities->count_total() ?>,
	"result": <?= json_encode($Entities->arrayify()) ?>
}
