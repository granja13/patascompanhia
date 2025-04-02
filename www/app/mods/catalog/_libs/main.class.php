<?php
session_start();


class Catalogo
{

    // <editor-fold defaultstate="collapsed" desc="Declaração de Variaveis">
    /**
     * Database::getInstance()
     * @var object 
     */
    private $db;

    /**
     * @deprecated unused ?
     * @var type 
     */
    private $marcas;

    /**
     * @deprecated unused ?
     * @var type 
     */
    private $categorias;
    private $login;
    // </editor-fold>


    /**
     * Construct da função 
     * 
     */
    public function __construct()
    {
        global $login;
        $this->db = Database::getConnection();
        $this->login = $login;
    }

    public function get_produto($referencia, $categoria = FALSE, $cart = FALSE, $perms = FALSE, $use_cache = true, $onlyprod = FALSE)
    {
        global $user_perms, $lg;
        $id = cleanInput($id);
        $onlineProdsDisplay = "'1'";
        if ($user_perms > 250 && $perms != FALSE) {
            $user_perms = $perms;
        }

        // Se perms for false nao entra na condiçao anterior
        if ($user_perms > 250) {
            $onlineProdsDisplay = "'1','0'";
        }

        if ($cart == FALSE && $categoria != FALSE) {
            $categ = "AND c.id_categ='$categoria'";
        } else {
            $categ = "AND c.id_prod='$id'";
        }

        //UPDATE prods SET pvpub_es = pvpub * 0.9, pvpro_es=pvpro*0.3, old_pvpub_es=old_pvpub, old_pvpro_es=old_pvpro, perc_pvpub_es=perc_pvpub, perc_pvpro_es=perc_pvpro
        list($preco, $pvpub) = $this->getVarPVP($user_perms);

        //isto nao esta a ser usado
        //$categoria=cleanInput($categoria);
        if ($user_perms >= 2) $perm = 2;
        else $perm = 1;

        $sql = "
        SELECT
        p.id,
        p.$pvpub as pvpub,
        p.old_$pvpub as old_pvpub,
        p.$preco as preco,
        p.old_$preco as old_preco,
        p.perc_$preco as perc,
        p.peso ,
        pc.*,
        p.stock,
        p.template,
        p.ean,
        p.marca,
        m.nome as nome_marca,
        c.id_categ,
        comentarios,
        stars,
        stock_lojas,
        p.embalagem,
        prom.tipo as excl_tipo,
        prom.valor as excl_valor,
        p.esconder_disponibilidade,
        p.produto_substituicao,
        p.acondicionamento,
        p.video,
        p.pai,
        IF(pc.color<>'', pc.color, p.`color`) AS color,
        p.`color_img`,
        p.`color_filter`,
        p.online
        FROM (prods p,prods_map_categ c,prods_campos pc,prods_marcas m,prods_categ pcc )
        LEFT JOIN prods_promocoes prom ON (p.id=prom.id_prod AND prom.data_de<=NOW() AND prom.data_ate>=NOW() AND prom.perms=$perm) 
        WHERE p.id='$id'  
            AND p.id=c.id_prod
            AND m.id=p.marca
            AND p.id=pc.id
            AND pc.lg='$lg'
            AND $preco>0.10 
            AND pcc.id=c.id_categ 
            $categ
            AND p.minperms<=$user_perms
            AND p.online IN ($onlineProdsDisplay)
            order by pcc.deep desc,pcc.id asc
        ";
        //if ($user_perms>250)
        //    echo $sql."<br/>";

        $cachename = "catalog_prod_" . $id . "__" . md5($sql);
        $data = $this->cache->getVar($cachename);
        if ($data === FALSE) {
            $row = $this->db->query("$sql")->fetch_first();
            if (DEBUG == TRUE) dg($this->db->last_query(), "sql");
            if (is_array($row)) {

                // All Attributes in search IDs
                $allAttributesProds = $this->getAttributesByIDs([$id]);

                // All Tags
                $tags_check = $this->getAllAttributesTags($lg, $user_perms);

                $row['imgs'] = b64unserialize($row['imgs']);
                $row['slug'] = slug_gen_prod($row['nome']) . "-" . $row['id'];
                $row['seo'] = b64unserialize($row['seo']);

                if ($row['acondicionamento'] > 1) {
                    $row['old_preco'] = $rows['old_preco'] * $row['acondicionamento'];
                    $row['pvpub'] = $row['pvpub'] * $row['acondicionamento'];
                    $row['old_pvpub'] = $row['old_pvpub'] * $row['acondicionamento'];
                    $row['preco'] = $row['preco'] * $row['acondicionamento'];
                }

                $row['atributos'] = $this->getAttributesByID($row['id'], $allAttributesProds);
                $row['tags'] = $this->checkAttributesTags($row['atributos'], $tags_check);

                // Ingredientes do produto
                $sql = "
        SELECT DISTINCT ing.nome, pi.descricao
        FROM `prods_ingredientes` pi
        JOIN `ingredientes` ing ON (ing.id=pi.id_ingrediente AND ing.lg='$lg') 
        WHERE pi.id_prod='$id'  
        ORDER BY pi.ordem asc
        ";
                $row['ingredientes'] = $this->db->query($sql)->fetch();

                // Promocoes exclusivas
                if ($row['excl_tipo'] == "perc" && $row['excl_valor'] > 0) {

                    $valor_aplicar = 1 - $row['excl_valor'] / 100;
                    if ($row['perc'] > 0)
                        $preco = $row['old_preco'];
                    else
                        $preco = $row['preco'];
                    $novo_preco = round(($preco * $valor_aplicar), 2, PHP_ROUND_HALF_UP);
                    $row['preco'] = $novo_preco;
                    $row['perc'] = intval($row['excl_valor']);
                    $row['old_preco'] = $preco;
                    $row[$user_perms < 2 ? "pvpub" : "pvpro"] = $novo_preco;
                    $row['exclusivo'] = 1;
                } else if ($row['excl_tipo'] == "base" && $row['excl_valor'] > 0) {
                    if ($row['perc'] > 0)
                        $preco = $row['old_preco'];
                    else
                        $preco = $row['preco'];
                    $novo_preco = $row['excl_valor'];
                    $row['preco'] = $novo_preco;
                    $perc = intval(100 - ($novo_preco * 100 / $preco));
                    $row['perc'] = $perc;
                    $row['old_preco'] = $preco;
                    $row[$user_perms < 2 ? "pvpub" : "pvpro"] = $novo_preco;
                    $row['exclusivo'] = 1;
                }
            }
            if ($use_cache == true)
                $this->cache->setVar($cachename, serialize($row), Cache::CACHE_HALF_HOUR);
        } else {
            $row = unserialize($data);
            //echo $_SERVER['REMOTE_ADDR'];
            if ($_SERVER['REMOTE_ADDR'] == "83.240.215.194") {
                //echo "<pre>";
                //print_r($row);
                // echo $sql;
                //echo "</pre>";

            }
        }

        if (!$onlyprod) {
            // Cores disponiveis
            $available_colors = $this->getAvailableColorsChild(($row['pai'] ?: $row['id']), $perm, $row['id']);
            if (!empty($available_colors) && count($available_colors) > 1) {
                $row['available_colors'] = (array)$available_colors;
            }
        }

        if (empty($row)) return FALSE;
        return $row;
    }
}
