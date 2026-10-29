<?php

class ClickUp
{
    private $apiKeyClickUp;
    private $listId;
    private $tagsQueNaoQuero = ['smartpos'];

    public function __construct()
    {
        $this->apiKeyClickUp = $_ENV['CLICKUP_API_KEY'];
        $this->listId = $_ENV['CLICKUP_LIST_ID'];
    }

    public function existeTask($chamado, $freshDesk)
    {
        if(strcasecmp($chamado['custom_fields']['cf_ambiente'], 'smart pos') === 0){
           $chamado['custom_fields']['cf_ambiente'] = 'smartpos';
        }

        // Não importar chamados do ambiente smartpos, não adiciona no clickUP.
          if (in_array($chamado['custom_fields']['cf_ambiente'], $this->tagsQueNaoQuero, true)) {
            echo "ID Freshdesk: {$chamado['id']}\n";
            echo "Domínio: {$chamado['domain']}\n";
            echo "Tag: {$chamado['custom_fields']['cf_ambiente']} \n";
            echo "Pertence a uma tag que não quero! Não entra! \n";
            echo "-------------------------\n";
            return true;
        }

        $pagina = 0;
        $dominios = $freshDesk->getDomain();

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
                $nomeTask = $task['name'] ?? '';
                $idChamado = $chamado['id'];

                $mesmoChamado = strpos($nomeTask, "#{$idChamado} -") === 0;

                //Se não é o mesmo ID, pula pra comparar com a próxima tarefa do clickup
                if(!$mesmoChamado){
                    continue;
                }

                $mesmaOrigem = false;

                foreach (($task['tags'] ?? []) as $tag) {
                    echo "Tag encontrada: {$tag['name']}\n";

                    if (
                        ($chamado['domain'] === $dominios[0] && $tag['name'] === 'mvgest') ||
                        ($chamado['domain'] === $dominios[1] && in_array($tag['name'], ['redeflex', 'grupocard'], true))
                    ) {
                        $mesmaOrigem = true;
                        break;
                    }
                }

                echo "ID Freshdesk: {$chamado['id']}\n";
                echo "Nome ClickUp: {$task['name']}\n";
                echo "Domínio: {$chamado['domain']}\n";
                echo "Mesma origem: " . ($mesmaOrigem ? 'SIM' : 'NÃO') . "\n";

                if ($mesmaOrigem) {
                    echo "Entrou na  mesma origem e mesmo chamado! \n";
                    echo "Não vai adicionar, pois já existe no clickUP! \n";
                    echo "-------------------------\n";
                    return true;
                }
                echo "-------------------------\n";
            
            }
            $pagina++;
        }

        //Não achou a task em nenhuma página retornada pela api do clickup
        return false;
    }

    public function criarTask($chamado, $solicitante)
    {
        //Verificando se a tag que vem é gestão para por no lugar mvgest
        if(strcasecmp($chamado['custom_fields']['cf_ambiente'], 'gestão') === 0){
            if(!empty($chamado['custom_fields']['cf_empresa'])){
                $chamado['custom_fields']['cf_ambiente'] = null;
            }else{
                $chamado['custom_fields']['cf_ambiente'] = 'mvgest';
            }
        }

        $tags = [];

        foreach (['cf_ambiente', 'cf_empresa'] as $campo) {
            $valor = $chamado['custom_fields'][$campo] ?? null;

            if ($valor !== null && $valor !== '') {
                $tags[] = ['name' => $valor];
            }
        }

        $task = [
            "name" => "#{$chamado['id']} - {$chamado['subject']}",
            "description" => "N° Chamado no FreshDesk: {$chamado['id']}\n" .
                "Solicitante: " . $solicitante['name'] . "\n" .
                "Descrição:\n{$chamado['description_text']}",
            "tags" => $tags,
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
