<?php
namespace Blog\Controller\Controllers;
use \Blog\Controller\Controller;
use \Blog\Model\DataObjects\Proposal;
use \Blog\Model\DataObjects\Lists\ProposalList;

class ProposalController extends Controller {
	const MODEL = Proposal::class;
	const LIST_MODEL = ProposalList::class;
}
?>
