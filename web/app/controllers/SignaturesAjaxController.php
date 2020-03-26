<?php
/**
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
declare(strict_types=1);

namespace Elabftw\Elabftw;

use Elabftw\Exceptions\DatabaseErrorException;
use Elabftw\Exceptions\FilesystemErrorException;
use Elabftw\Exceptions\IllegalActionException;
use Elabftw\Exceptions\ImproperActionException;
use Elabftw\Exceptions\InvalidCsrfTokenException;
use Elabftw\Exceptions\UnauthorizedException;
use Elabftw\Models\Database;
use Elabftw\Models\Experiments;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for the experiments signatures
 *
 */
require_once \dirname(__DIR__) . '/init.inc.php';

$Response = new JsonResponse();
$Response->setData(array(
    'res' => true,
    'msg' => _('Sent'),
));

try {
    if ($App->Session->has('anon')) {
        throw new IllegalActionException('Anonymous user tried to access database controller.');
    }

    // CSRF
    $App->Csrf->validate();

    if ($Request->request->get('type') === 'experiments') {
        $Entity = new Experiments($App->Users);
    } else {
        $Entity = new Database($App->Users);
    }

    // CREATE
    if ($Request->request->has('create')) {
        $revnum = (int) $Request->request->get('revnum');
        $Entity->setId((int) $Request->request->get('id'));
        $signatureId = $Entity->Signatures->create($revnum);
        $Response->setData(array(
            'res' => true,
            'msg' => _('Signed'),
            'id' => $signatureId,
        ));
    }

    // UPDATE
    if ($Request->request->has('update')) {
        // get the id from the sent value (signature_42)
        $exploded = \explode('_', $Request->request->get('id'));
        $id = (int) $exploded[1];
        $res = $Entity->Signatures->update($Request->request->get('update'), $id);
        $Response->setData(array(
            'res' => true,
            'msg' => _('Signature updated'),
            'update' => $res,
        ));
    }

    // DESTROY
    if ($Request->request->has('destroy')) {
        $Entity->Signatures->destroy((int) $Request->request->get('id'));
    }
} catch (ImproperActionException | InvalidCsrfTokenException | UnauthorizedException $e) {
    $Response->setData(array(
        'res' => false,
        'msg' => $e->getMessage(),
    ));
} catch (IllegalActionException $e) {
    $App->Log->notice('', array(array('userid' => $App->Session->get('userid')), array('IllegalAction', $e)));
    $Response->setData(array(
        'res' => false,
        'msg' => Tools::error(true),
    ));
} catch (DatabaseErrorException | FilesystemErrorException $e) {
    $App->Log->error('', array(array('userid' => $App->Session->get('userid')), array('Error', $e)));
    $Response->setData(array(
        'res' => false,
        'msg' => $e->getMessage(),
    ));
} catch (Exception $e) {
    $App->Log->error('', array(array('userid' => $App->Session->get('userid')), array('Exception' => $e)));
} finally {
    $Response->send();
}
