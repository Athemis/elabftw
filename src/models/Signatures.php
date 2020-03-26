<?php
/**
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
declare(strict_types=1);

namespace Elabftw\Models;

use Elabftw\Elabftw\Db;
use Elabftw\Elabftw\Tools;
use Elabftw\Exceptions\ImproperActionException;
use Elabftw\Interfaces\CrudInterface;
use PDO;

/**
 * All about the signatures
 */
class Signatures implements CrudInterface
{
    /** @var AbstractEntity $Entity instance of Experiments or Database */
    public $Entity;

    /** @var Db $Db SQL Database */
    protected $Db;

    /**
     * Constructor
     *
     * @param AbstractEntity $entity
     */
    public function __construct(AbstractEntity $entity)
    {
        $this->Db = Db::getConnection();
        $this->Entity = $entity;
    }

    /**
     * Create a signature
     *
     * @return int signature id
     */
    public function create(int $revision_id): int
    {
        $sql = 'INSERT INTO ' . $this->Entity->type . '_signatures(datetime, item_id, revision_id, userid)
            VALUES(:datetime, :item_id, :revision_id, :userid)';
        $req = $this->Db->prepare($sql);
        $req->bindValue(':datetime', date('Y-m-d H:i:s'));
        $req->bindParam(':item_id', $this->Entity->id, PDO::PARAM_INT);
        $req->bindParam(':revision_id', $revision_id, PDO::PARAM_INT);
        $req->bindParam(':userid', $this->Entity->Users->userData['userid'], PDO::PARAM_INT);

        $this->Db->execute($req);

        return $this->Db->lastInsertId();
    }

    /**
     * Read signature for an entity id
     *
     * @return array signature for this entity
     */
    public function readAll(): array
    {
        $sql = 'SELECT ' . $this->Entity->type . "_signatures.*,
            CONCAT(users.firstname, ' ', users.lastname) AS fullname
            FROM " . $this->Entity->type . '_signatures
            LEFT JOIN users ON (' . $this->Entity->type . '_signatures.userid = users.userid)
            WHERE item_id = :id ORDER BY ' . $this->Entity->type . '_signatures.datetime ASC';
        $req = $this->Db->prepare($sql);
        $req->bindParam(':id', $this->Entity->id, PDO::PARAM_INT);
        $this->Db->execute($req);
        $res = $req->fetchAll();
        if ($res === false) {
            return array();
        }
        return $res;
    }

    /**
     * Update a signature
     *
     * @param int $id id of the signature
     * @return string
     */
    public function update(int $id): string
    {
        $sql = 'UPDATE ' . $this->Entity->type . '_signatures SET
            datetime = :datetime
            WHERE id = :id AND userid = :userid';
        $req = $this->Db->prepare($sql);
        $req->bindValue(':datetime', date('Y-m-d H:i:s'));
        $req->bindParam(':id', $id, PDO::PARAM_INT);
        $req->bindParam(':userid', $this->Entity->Users->userData['userid'], PDO::PARAM_INT);
        $res = $this->Db->execute($req);

        return $res;
    }

    /**
     * Destroy a signature
     *
     * @param int $id id of the signature
     * @return void
     */
    public function destroy(int $id): void
    {
        $sql = 'DELETE FROM ' . $this->Entity->type . '_signatures WHERE id = :id AND userid = :userid';
        $req = $this->Db->prepare($sql);
        $req->bindParam(':id', $id, PDO::PARAM_INT);
        $req->bindParam(':userid', $this->Entity->Users->userData['userid'], PDO::PARAM_INT);
        $this->Db->execute($req);
    }
}
