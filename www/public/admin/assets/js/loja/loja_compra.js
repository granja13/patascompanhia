$(document).ready(function() {

    $("#lista_encomendas").DataTable({
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "pageLength": 25,
        "buttons": ["copy", "excel", "pdf", "print"],
        ajax:{
            url: "../admin/api/json.loja_compra.php"
        },
        language: {
              processing: "A processar...",
              lengthMenu: "Mostrar _MENU_ registos",
              zeroRecords: "Não foram encontrados resultados",
              info: "Mostrando de _START_ até _END_ de _TOTAL_ registos",
              infoEmpty: "Mostrando de 0 até 0 de 0 registos",
              infoFiltered: "(filtrado de _MAX_ registos no total)",
              search: "Procurar:",
              paginate: {
                  first:    "<<",
                  previous: "<",
                  next:     ">",
                  last:     ">>"
              }
          },
          columns: [
            {'data': 'id'},
            {'data': 'nome'},
            {'data': 'data'},
            {'data': 'status_encomenda'},
            {'data': 'envio_tipo'},
            {'data': 'envio_pagamento'},
            {'data': 'total'},
            {
                'data': null,
                'class': "text-center",
                'render': function (data, type, row, meta) {
                    let html = '';
                    html += "<a href='loja_compra_edita.php?id=" + row.id + "' ><button class='btn btn-primary'><i class='fas fa-pencil-alt'></i></button></a>";
                    return html;
                }

            },
          ],
      })

});
