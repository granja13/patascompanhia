$(document).ready(function() {

    function getParameterByName(name) {
        let url = new URL(window.location.href);
        return url.searchParams.get(name);
    }

    let mes = getParameterByName('mes');

    let tabela = $("#lista_vendas_detalhe").DataTable({
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "pageLength": 25,
        ajax:{
            url: "../admin/api/json.report_vendas_detalhe.php",
            data: {
                mes: mes
            }
        },
        columns: [
            {'data': 'data_venda'},
            {'data': 'cliente'},
            {'data': 'produto'},
            {'data': 'quantidade'},
            {'data': 'preco'},
            {
                'data': null, // Não é um campo direto do banco, vamos calcular
                'render': function(data, type, row) {
                    return (row.quantidade * row.preco).toFixed(2); // Calcula o total
                }
            },
        ],
        language: {
          processing: "A processar...",
          lengthMenu: "Mostrar _MENU_ registos",
          zeroRecords: "Não foram encontrados resultados",
          info: "Mostrando de _START_ até _END_ de _TOTAL_ registos",
          infoEmpty: "Mostrando de 0 até 0 de 0 registos",
          infoFiltered: "(filtrado de _MAX_ registos no total)",
          search: "Procurar:",
          paginate: {
              first: "<<",
              previous: "<",
              next: ">",
              last: ">>"
          }
        }
    });

});
