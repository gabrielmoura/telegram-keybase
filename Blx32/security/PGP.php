<?php
/**
 * @author Gabriel Moura <blx32@srmoura.com.br>
 * @copyright 2015-2017 SrMoura
 */
namespace Blx32\security;


class PGP
{
    public function _contruct()
    {
        $this->pgp = new gnupg();
    }
}