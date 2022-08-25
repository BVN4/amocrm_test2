<?php

include 'init.php';
/** @var Auth $provider */
/** @var array $ini */

$accessToken = $provider->getToken();

$provider->setBaseDomain($accessToken->getValues()['baseDomain']);

$page = $_GET['page'] ?? 1;
if($page < 1) $page = 1;

$maxRows = 250;

$leads = getData('leads', [
	'limit' => $maxRows,
	'page' => $page,
	'with' => 'contacts',
]);

$additionalData = [
	'contacts' => [],
	'companies' => []
];

/**
 * Перебор всех сделок и поиск связанных контактов и компаний.
 * ID найденных связей помещаются в массив, который при запросе используется в качестве фильтра, чтобы одним запросом получить все нужные связанные сущности.
 */
foreach($additionalData as $type => $additionalArray){
	$ids = [];

	foreach($leads as $lead){
		foreach($lead['_embedded'][$type] as $additionalItem){
			$ids[] = $additionalItem['id'];
		}
	}

	$additionalData[$type] = [];

	$p = 0;
	while(true){
		++$p;
		$query = [
			'limit' => 250,
			'page' => $p,
			'filter' => [ 'id' => $ids ]
		];

		$raw = getData($type, $query);
		foreach($raw as $item){
			$additionalData[$type][$item['id']] = $item;
		}

		if(count($raw) < 250) break;
	}
}

/**
 * Возвращает записи из API
 * @param string $type
 * @param array $query
 * @return array
 */
function getData(string $type, array $query): array
{
	global $accessToken;
	global $provider;

	try{
		$data = $provider->getHttpClient()
			->request('GET', $provider->urlAccount() . 'api/v4/'.$type, [
				'headers' => $provider->getHeaders($accessToken),
				'query' => $query,
			]);

		$parsedBody = json_decode($data->getBody()->getContents(), true);

		return (array) $parsedBody['_embedded'][$type];
	}catch(GuzzleHttp\Exception\GuzzleException $e){
		var_dump((string)$e);
		return [];
	}
}

?>

<style>
	body {
		padding: 0;
		margin: 0;
	}

	table {
        width: 100%;
        text-align: center;
	}

	table thead {
        position: sticky;
        height: 50px;
        top: 0;

        background: #fff;
        box-shadow: 0 4px 2px -3px #0003;
	}

    table tbody tr:nth-child(2n) { background: #0001; }

	table tbody tr span {
		padding: 0 4px;
		margin: 0 5px;
	}

	table tbody tr span.type-True {
		background: #4caf5055;
		color: #114413;
	}

	table tbody tr span.type-False {
		background: #f4433655;
		color: #5a140f;
	}

	.links {
		text-align: center;
		font-size: 1.5rem;
	}

	.links a {
        margin: 0 2rem;
	}

    .links a[disabled] {
        color: #0005;
		pointer-events: none;
    }
</style>

<table>
	<thead>
		<tr>
			<th>ID</th>
			<th>Lead</th>
			<th>Company</th>
			<th>Contact</th>
			<th>Custom field</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($leads as $lead){ ?>
			<tr>
				<td><?php echo $lead['id'] ?></td>
				<td><?php echo $lead['name'] ?></td>

				<?php foreach($additionalData as $type => $data){
					echo '<td>';
					foreach($lead['_embedded'][$type] as $item){
						echo $data[$item['id']]['name'].'<br>';
					}
					echo '</td>';
				} ?>

				<td><?php
					foreach($lead['_embedded']['contacts'] as $item){
						foreach($additionalData['contacts'][$item['id']]['custom_fields_values'] as $field){
							if($field['field_code'] === $ini['custom_field_code']){
								foreach($field['values'] as $value){
									echo '<span class="type-'.$value['value'].'">'.$value['value'].'</span>';
								}
								break;
							}
						}
					}
					?></td>

			</tr>
		<?php } ?>
	</tbody>
</table>

<div class="links">
	<a href="?page=<?php echo $page - 1 ?>" <?php if($page <= 1){ echo 'disabled'; } ?>>< Prev</a>
	<a href="?page=<?php echo $page + 1 ?>" <?php if(count($leads) < $maxRows){ echo 'disabled'; } ?>>Next ></a>
</div>

