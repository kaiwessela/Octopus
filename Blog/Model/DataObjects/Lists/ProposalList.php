<?php
namespace Blog\Model\DataObjects\Lists;
use \Blog\Model\Abstracts\DataObjectList;
use \Blog\Model\DataObjects\Proposal;

class ProposalList extends DataObjectList {

#	@inherited
#	public $objects;	{alias $proposals}
#	public $count;
#
#	private $new;
#	private $empty;

	const OBJECT_CLASS = Proposal::class;
	const OBJECTS_ALIAS = 'proposals';


	const SELECT_QUERY = <<<SQL
SELECT * FROM proposals
ORDER BY proposal_timestamp DESC
SQL; #---|

	const COUNT_QUERY = <<<SQL
SELECT COUNT(*) FROM proposals
SQL; #---|

}
?>
