<?php

class FreshDesk
{
    private $domain = [];
    private $apiKeyFreshDesk = [];

    public function __construct()
    {
        $this->domain[0] = $_ENV['FRESHDESK_DOMAIN_RMV'];
        $this->domain[1] = $_ENV['FRESHDESK_DOMAIN_SIMPLESMP'];
        $this->apiKeyFreshDesk[0] = $_ENV['FRESHDESK_API_KEY_RMV'];
        $this->apiKeyFreshDesk[1] = $_ENV['FRESHDESK_API_KEY_SIMPLESMP'];
    }

    public function getDomain()
    {
        return $this->domain;
    }
    public function listarChamados()
    {
        $chamadosAbertos = [];

        for($contador = 0; $contador < count($this->domain); $contador++){
            //Cria o cliente HTTP
            $curl = curl_init();

            //Quero acessar este url, estou ordenando pela data de criação pegando os 30 mais recentes com status 2 = aberto
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://' . $this->domain[$contador] . '.freshdesk.com/api/v2/search/tickets?query="status:2"&page=1',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => $this->apiKeyFreshDesk[$contador] . ':X',
            ]);

            $respostaDaAPIFreshDesk = curl_exec($curl);

            $resposta = json_decode($respostaDaAPIFreshDesk, true);
            //Fecho o cliente http
            curl_close($curl);

            $resposta['domain'] = $this->domain[$contador];

             foreach (($resposta['results'] ?? []) as $chamado) {
                $chamado['domain'] = $this->domain[$contador];
                $chamadosAbertos[] = $chamado;
            }
        }

        return $chamadosAbertos;
    }

    public function getSolicitante($idSolicitante)
    {

        for($contador = 0; $contador < count($this->domain); $contador++){
            // Cria o cliente HTTP
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://' . $this->domain[$contador] . '.freshdesk.com/api/v2/contacts/' . $idSolicitante,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => $this->apiKeyFreshDesk[$contador] . ':X',
            ]);

            $respostaDaAPIFreshDesk = curl_exec($curl);

            curl_close($curl);

            $solicitante = json_decode($respostaDaAPIFreshDesk, true);

            if(!empty($solicitante)){
                return $solicitante;
            }
        }
        
        return null;
    }
}
