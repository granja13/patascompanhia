<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

/**
 * @requires object Catalogo
 */

class Cart
{
    /**
     * Database::getInstance()
     *
     * @var object
     */
    private $db;

    /**
     * New Catalogo();
     *
     * @var object
     */
    private $catalogo;

    /**
     * Utilizador atual
     *
     * @var string
     */
    private $username;

    /**
     * IP do utilizador atual
     *
     * @var string
     */
    private $client_ip;

    /**
     * Stock minimo para o stock ficar online
     *
     * @var integer
     */
    private $stock_min;

    /**
     * is admin or not
     *
     * @var boolean
     */
    private $admin;

    /**
     * is bot or not
     *
     * @var boolean
     */
    private $is_crawler;

    /**
     * session_id
     *
     * @var string
     */
    public $session_id;

    /**
     * Id do cliente
     *
     * @var integer
     */
    public $id_cliente;

    /**
     * Id CEG do cliente
     *
     * @var integer
     */
    public $id_ceg_cliente;

    /**
     * Permissoes do utilizador - 0 < $perms < 256
     *
     * @var integer
     */
    public $perms;

    /**
     * id do carrinho
     *
     * @var integer
     */
    public $cart_id;

    /**
     * Valor total do carrinho
     *
     * @var float
     */
    public $total_preco;

    /**
     * id do tipo de envio no carrinho
     *
     * @var integer
     */
    public $envio;

    /**
     * id da tarifa aplicada no carrinho
     *
     * @var integer
     */
    public $tarifa;

    /**
     * total de peso dos artigos do carrinho em gramas
     *
     * @var integer
     */
    public $total_peso;

    /**
     * Numero de items no carrinho
     *
     * @var integer
     */
    public $total_items;

    /**
     * Numero de produtos diferentes no carrinho
     *
     * @var integer
     */
    public $total_prods;

    /**
     * Valor total dos portes calculados
     *
     * @var float
     */
    public $total_portes;

    /**
     * Listas de produtos contidas no carrinho
     *
     * @var array
     */
    public $listas_prods;

    /**
     * Produtos no carrinho que não tem stock
     *
     * @var array
     */
    public $produtos_sem_stock;

    /**
     * Produtos que sofreram alteração de quantidades
     *
     * @var array
     */
    public $produtos_qty_alterada;

    /**
     * Valor da oferta de portes >0 caso seja portes gratis
     *
     * @var float
     */
    public $oferta_portes;

    /**
     * Promocoes aplicadas no carrinho
     *
     * @var array
     */
    public $promocoes;

    /**
     * Array da lista de clientes usada
     *
     * @var array
     */
    public $lista_clientes;

    /**
     * Variavel geral do carrinho e todos os dados
     *
     * @var array
     */
    public $carrinho;

    /**
     * id do cliente com login feito
     *
     * @var integer
     */
    public $cliente;

    /**
     * Vouchers aplicados no carrinho
     *
     * @var array
     */
    public $vouchers;

    /**
     * Valor dos portes oferecidos
     *
     * @var float
     */
    public $oft_portes_sap;

    /**
     * Sistema de templates
     *
     * @var Object
     */
    private $template;

    /**
     * Informa se o carrinho foi destruido
     *
     * @var bool
     */
    public $destroyedCart = false;

    /**
     * Informa se é com ou sem IVA
     *
     * @var bool
     */
    public $withoutTax = false;

    public $referralBalance = 0;

    public $shipping_type = 0;

    public $shipping_carrier = 0;

    public $shipping_country = "PT";

    public $shipping_details = null;
    // </editor-fold>


    /**
     * Inicializador da class
     *
     * @global type $current_user
     * @global type $stock_min
     * @global type $user_details
     * @global type $user_perms
     * @global type $_SESSION
     * @return type
     */

    private $user_perms;
    private $login;

    public function __construct()
    {
        global $current_user, $stock_min, $user_details, $user_perms, $_SESSION, $smarty, $login, $catalogo;


        $this->db = Database::getConnection();
        //$this->cache = Cache::getInstance();
        $this->catalogo = new Catalogo();
        $this->template = $smarty;

        $this->user_perms = $login->perms;
        $this->login = $login;

        // Bots dont need cart ;)
        //$this->is_crawler = crawlerDetect();

        //if ($this->is_crawler === TRUE) return;

        $this->vouchers = (is_array($_SESSION['vouchers'])) ? $_SESSION['vouchers'] : array();

        $this->session_id = session_id();
        $this->username = $current_user;
        $this->id_cliente = $user_details['id'];
        $this->id_ceg_cliente = $user_details['id_ceg'];
        //$this->cart_id = $this->cart_idf();
        //$this->client_ip = getClientIP();

        $this->stock_min = $stock_min;
        $this->perms = $user_perms;
        $this->carrinho = $this->get_cart();

    }

    public function get_cart($id_cart = false, $tipo = FALSE)
    {
        global $slug, $slugs;
        $this->oferta_portes = 0;
        $this->referralBalance = 0;
        $this->produtos_sem_stock=[];
        $this->produtos_qty_alterada=[];

        /*if ($this->perms < 200) {
            // Admin perms can view any cart
            $id_cart = $this->cart_id;
        } else {
            if ($id_cart > 0) {
                $this->cart_id = $id_cart;
                $this->perms = $tipo;
                unset($this->vouchers);
                $this->admin = TRUE;
            } else {
                $id_cart = $this->cart_id;
            }

        }*/

        if (!($id_cart > 0)) return;
        if (!$id_cart) return;
        // Search engine dont need cart ;)
        if ($this->is_crawler === TRUE) return;
        $sql = "SELECT i.*,c.* FROM carrinho_items i,carrinho c WHERE i.id_cart='$id_cart' AND c.id=i.id_cart";
        $result = $this->db->query($sql)->fetch();
        
        //print_r(sizeof($result));
        /*if (sizeof($result) == 0) {
            $this->destroy($id_cart);
            return;
        }*/

        $sql_last_cart = "SELECT `id`, ROUND(`total`, 2) AS 'total_precos' FROM `carrinho` WHERE id = $id_cart ";
        $last_cart = $this->db->query($sql_last_cart)->fetch();
        //$last_cart = (array) Database::getNette()->query("SELECT `id`, ROUND(`total`, 2) AS 'total_precos' FROM `carrinho` WHERE id = ? ", $id_cart)->fetch();
        $last_cart['total_prods'] =  (int)count($result);
        $last_cart['total_items'] =  (int)array_sum(array_column($result, 'qty'));
        //$prodsOld = array_column($result, 'qty', 'id_prod');

        $this->total_preco = 0;
        $this->total_peso = 0;
        $this->total_items = 0;
        $total_prods = 0;
        $ret = array();

        foreach ($result as $key => $row) {

            $produto = $this->catalogo->get_produto($row['referencia'], FALSE, TRUE, $tipo, true,true);
           
            $this->cliente = $row['id_cliente'];
            if (is_array($produto)) {

                //$produto = (checkProdImagesAndResize([$produto]))[0];

                $stock_disponivel = $produto['stock'] - $this->stock_min;

                $old_qty = $row['qty'];
                if (($stock_disponivel) < $row['qty']) {
                    $qty = $stock_disponivel;

                    //Logica alertas de quantidade e sem stock
                    if($qty > 0) {
                        if (!isset($this->produtos_qty_alterada[$row['id_prod']])) {
                            $this->produtos_qty_alterada[$row['id_prod']] = [
                                'id' => $produto['id'],
                                'nome' => $produto['nome'],
                                'qtd_old' => (int)$old_qty,
                                'qtd_new' => (int)$qty
                            ];
                        }
                    } else {
                        if (!isset($this->produtos_sem_stock[$row['id_prod']])) {
                            $this->produtos_sem_stock[$row['id_prod']] = [
                                'id' => $produto['id'],
                                'nome' => $produto['nome'],
                                'qtd_old' => (int)$old_qty,
                                'qtd_new' => 0,
                            ];
                        }
                    }
                } else {
                    $qty = $row['qty'];
                }
                //echo $stock_disponivel.",".$row['qty']."-";
                if ($qty <= 0) {
                    $qty = 0;
                    if (empty($this->carrinho)) {
                        continue;
                    }
                }

                $deleted = 0;
                if ($qty != $row['qty']) {
                    $this->set_envio(0, 0, 0, 0, 0);
                    $where = array(
                        'id_cart' => $this->cart_id,
                        'id_prod' => $produto['id'],
                    );
                    $update = array("qty" => $qty);
                    $this->db->where($where)->limit(1)->update("cart_items", $update);
                    $row['qty'] = $qty;
                }

                $ret[$key]['preco'] = $produto['preco'];
                $ret[$key]['old_preco'] = $produto['old_preco']; // preço anterior conforme pro e pub
                $ret[$key]['peso'] = $produto['peso'] * $row['qty'];
                $ret[$key]['envio'] = $row['envio'];
                $this->envio = explode(":", $row['envio']);
                $ret[$key]['qty'] = $row['qty'];
                $produto['nome'] = ($produto['embalagem'] != '' ? $produto['nome'] = $produto['nome'] . " - " . $produto['embalagem'] : $produto['nome']);
                $ret[$key]['nome'] = $produto['nome'];
                $ret[$key]['acondicionamento'] = $produto['acondicionamento'];
                $ret[$key]['imgs'] = $produto['imgs'];
                $ret[$key]['id'] = $produto['id'];
                $ret[$key]['stock'] = $produto['stock'];
                $ret[$key]['id_categ'] = $produto['id_categ'];
                $ret[$key]['subtotal'] = $produto['preco'] * $row['qty'];
                $ret[$key]['marca'] = $produto['marca'];
                if ($qty == 0 && $produto['stock'] <= 0) {
                    if (!isset($ret[$key]['nome']) || $ret[$key]['nome'] !== $produto['nome']) {
                        $ret[$key]['nome'] = $produto['nome'];
                    }
                }
                $total_prods++;
                $this->total_preco = ($produto['preco'] * $row['qty']) + $this->total_preco;
                $this->total_peso = ($produto['peso'] * $row['qty']) + $this->total_peso;
                $this->total_items = $row['qty'] + $this->total_items;

                $sql2 = "SELECT l.id,l.nome FROM listas_prods p,listas l WHERE p.id_prod='{$row['id_prod']}' and l.id=p.id";
                $cachename = "mysql_cart_get_cart_var_res_listas__" . (md5($sql2));
                // Get the cache for $cachename if it exists
                $data = $this->cache->getVar($cachename);
                if ($data === FALSE) {
                    $res_listas = $this->db->query($sql2)->fetch();
                    $this->cache->setVar($cachename, json_encode($res_listas), Cache::CACHE_FIVE_MINUTES);
                } else {
                    // The data was retrieved from the cache, so save it in a local variable for use later
                    $res_listas = json_decode($data, true);
                }
                foreach ($res_listas as $key2 => $res_lista) {
                    $lista[$res_lista['id']]['nome'] = $res_lista['nome'];
                    $lista[$res_lista['id']]['total'] = $lista[$res_lista['id']]['total'] + $ret[$key]['subtotal'];
                    $lista[$res_lista['id']]['peso'] = $lista[$res_lista['id']]['peso'] + $ret[$key]['peso'];
                    $lista[$res_lista['id']]['qty'] = $lista[$res_lista['id']]['qty'] + $row['qty'];
                    $lista[$res_lista['id']]['prods'][] = $ret[$key];
                    for ($i = 1; $i <= $row['qty']; $i++) {
                        $lista[$res_lista['id']]['precos'][] = $ret[$key]['preco'];
                    }
                    // Comentado e passou para uma so vez mais abaixo por causa do erro de lentidao do add cart
//                    if (is_array($lista[$res_lista['id']]['precos'])) {
//                        sort($lista[$res_lista['id']]['precos']);
//                    }

                    // Maior preço
                    if ($ret[$key]['preco'] > $lista[$res_lista['id']]['maior'] || !isset($lista[$res_lista['id']]['maior']))
                        $lista[$res_lista['id']]['maior'] = $ret[$key]['preco'];
                    // Menor preço
                    if ($ret[$key]['preco'] < $lista[$res_lista['id']]['menor'] || !isset($lista[$res_lista['id']]['menor']))
                        $lista[$res_lista['id']]['menor'] = $ret[$key]['preco'];

                }

                ///////////////////////////// CREATE A LIST OF ALL PRODUCTS
                $lista['all']['nome'] = "All Products";
                $lista['all']['total'] = $lista['all']['total'] + $ret[$key]['subtotal'];
                $lista['all']['peso'] = $lista['all']['peso'] + $ret[$key]['peso'];
                $lista['all']['qty'] = $lista['all']['qty'] + $row['qty'];
                $lista['all']['prods'][] = $ret[$key];
                for ($i = 1; $i <= $row['qty']; $i++) {
                    $lista['all']['precos'][] = $ret[$key]['preco'];
                }
                // Comentado e passou para uma so vez mais abaixo por causa do erro de lentidao do add cart
//                if (is_array($lista['all']['precos'])) {
//                    sort($lista['all']['precos']);
//                }
                // Maior preço
                if ($ret[$key]['preco'] > $lista['all']['maior'] || !isset($lista['all']['maior']))
                    $lista['all']['maior'] = $ret[$key]['preco'];
                // Menor preço
                if ($ret[$key]['preco'] < $lista['all']['menor'] || !isset($lista['all']['menor']))
                    $lista['all']['menor'] = $ret[$key]['preco'];
                ///////////////////////////////////////////////////////////////////
                $tot = $row['total'];
                $oferta_portess = $row['oferta_portes'];
                $portess = $row['portes'];
            } else {
                $this->del($row['id_prod']);
            }
        }

        return $ret;
    }


}