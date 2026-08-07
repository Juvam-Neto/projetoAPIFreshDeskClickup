<?php

class FreshDesk
{
    private $domain;
    private $apiKeyFreshDesk;

    public function __construct()
    {
        $this->domain = $_ENV['FRESHDESK_DOMAIN'];
        $this->apiKeyFreshDesk = $_ENV['FRESHDESK_API_KEY'];
    }

    public function listarChamados()
    {
        //Cria o cliente HTTP
        $curl = curl_init();

        //Quero acessar este url, estou ordenando pela data de criação pegando os 30 mais recentes com status 2 = aberto
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://'.$this->domain.'.freshdesk.com/api/v2/search/tickets?query="status:2"&page=1',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $this->apiKeyFreshDesk . ':X',
        ]);

        $respostaDaAPIFreshDesk = curl_exec($curl);

        //Fecho o cliente http
        curl_close($curl);

        $chamadosAbertos = json_decode($respostaDaAPIFreshDesk, true);

        return $chamadosAbertos['results'];
    }

}
?>