<?php

class ClickUp
{
    private $apiKeyClickUp;
    private $listId;


    public function __construct()
    {
        $this->apiKeyClickUp = $_ENV['CLICKUP_API_KEY'];
        $this->listId = $_ENV['CLICKUP_LIST_ID'];
    }

    public function existeTask($id)
    {
        $pagina = 0;
        while (true) {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.clickup.com/api/v2/list/{$this->listId}/task?page={$pagina}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: {$this->apiKeyClickUp}"
                ]
            ]);

            $resposta = curl_exec($curl);

            curl_close($curl);


            $tarefas = json_decode($resposta, true);

            //Significa que acabaram as tasks em todas páginas
            if (empty($tarefas['tasks'])) {
                break;
            }

            foreach ($tarefas['tasks'] as $task) {

                if (str_contains($task['name'], "#{$id} -")) {
                    return true;
                }
            }
            $pagina++;
        }

        //Não achou a task em nenhuma página retornada pela api do clickup
        return false;
    }

    public function criarTask($id, $titulo, $descricaoDetalhada, $solicitante, $customFields)
    {
        //Verificando se a tag que vem é gestão para por no lugar mvgest
        if(strcasecmp($customFields['cf_ambiente'], "gestão") === 0){
            $customFields['cf_ambiente'] = "mvgest";
        }

        $task = [
            "name" => "#{$id} - {$titulo}",
            "description" => "N° Chamado no FreshDesk: {$id}\n" .
                "Solicitante: " . $solicitante['name'] . "\n" .
                "Descrição:\n{$descricaoDetalhada}",
            "tags" => [
                [
                    "name" => $customFields['cf_ambiente']
                ]
            ],
            "status" => "backlog"
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.clickup.com/api/v2/list/{$this->listId}/task",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($task),
            CURLOPT_HTTPHEADER => [
                "Authorization: {$this->apiKeyClickUp}",
                "Content-Type: application/json"
            ]
        ]);

        $respostaAPIClickUp = curl_exec($curl);

        curl_close($curl);

        return json_decode($respostaAPIClickUp, true);
    }
}
