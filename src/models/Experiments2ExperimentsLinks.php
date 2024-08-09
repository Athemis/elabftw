<?php

/**
 * @author Nicolas CARPi <nico-git@deltablot.email>
 * @copyright 2024 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */

declare(strict_types=1);

namespace Elabftw\Models;

/**
 * All about Experiments Links in Experiments
 */
class Experiments2ExperimentsLinks extends AbstractExperimentsLinks
{
    protected function getTable(): string
    {
        return 'experiments2experiments';
    }
}