<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Column;
use \Blog\Model\DataObjects\Lists\ColumnList;

class ColumnController extends Controller {
	const MODEL = Column::class;
	const LIST_MODEL = ColumnList::class;

	const PAGINATABLE = true;
}
?>
