<?php
/**
 * @author Gabriel Moura <g@srmoura.com.br>
 * @copyright 2015-2017 SrMoura
 */
namespace Blx32\keybase;


class Keybase
{
    ///////////////////////////
    ///  Lookup Info.User /////
    ///////////////////////////

    /**
     * @param $id
     * @return mixed
     */
    public function full_name($id)
    {
        return $this->lookup('usernames', $id)->them[0]->profile->full_name;
    }

    /**
     * @param $id
     * @return mixed
     * Só funciona quando logado.
     */
    public function email($id)
    {
        return $this->lookup('usernames', $id)->them[0]->emails->primary->email;
    }

    /**
     * @param $id
     * @return string
     */
    public function key_fingerprint($id)
    {
        return strtoupper(chunk_split(substr(($this->lookup('usernames', $id)->them[0]->public_keys->primary->key_fingerprint), -16), 4, ' '));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function location($id)
    {
        return $this->lookup('usernames', $id)->them[0]->profile->location;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function biography($id)
    {
        return $this->lookup('usernames', $id)->them[0]->profile->bio;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function picture($id)
    {
        return $this->lookup('usernames', $id)->them[0]->pictures->primary->url;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function code($id){
        return $this->lookup('username', $id)->status->name;
    }
    /**
     * @param $method
     * @param $id
     * @return mixed
     */
    public function lookup($method, $id)
    {
        $data = 'lookup.json?' . $method . '=' . $id;
        return json_decode($this->endpoint($data, false));
    }
    ///////////////////////////
    //////  Discover /////
    ///////////////////////////

    /**
     * @param $method
     * @param $id
     * @return mixed
     */
    public function discover($method, $id)
    {
        return $this->endpoint('discover.json?' . $method . '=' . $id);
    }

    ///////////////////////////
    //// Export Public Key ////
    ///////////////////////////

    /**
     * @param $id
     * @return mixed
     */
    public function export($id)
    {
        return $this->endpoint($id, true);
    }

    ///////////////////////////
    //////  Auto Complete /////
    ///////////////////////////
    /**
     * @param $method
     * @param $id
     * @return mixed
     */
    public function autocomplete($method, $id)
    {
        return $this->endpoint('autocomplete.json?' . $method . '=' . $id);
    }



    /////////////////////////
    /////     ENDPONT   /////
    /////////////////////////
    /**
     * @param $Url
     * @return mixed
     */
    private function url_get_contents($Url)
    {
        if (!function_exists('curl_init')) {
            die('CURL is not installed!');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $Url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * @param $api
     * @param bool $export
     * @return mixed
     */
    public function endpoint($api, $export = false)
    {
        if (!$export) {
            $url = 'https://keybase.io/_/api/1.0/user/' . $api;
            return $this->url_get_contents($url);
        } else {
            return $this->url_get_contents('https://keybase.io/' . $api . '/pgp_keys.asc');
        }
    }

}