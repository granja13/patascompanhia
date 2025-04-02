$(document).ready(function() {

    $("#lista_carrinhos").DataTable({
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "pageLength": 25,
        "buttons": ["copy", "excel", "pdf", "print"],
        ajax:{
            url: "../admin/api/json.loja_carrinhos.php"
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
            {'data': 'last_login'},
            {'data': 'ip'},
            {'data': 'data_criado'},
            {'data': 'data_update'},
            {'data': 'total'},
            {
                'data': null,
                'class': "text-center",
                'render': function (data, type, row, meta) {
                    let html = '';
                    html += "<a href='loja_carrinho.php?id=" + row.id + "' ><button class='btn btn-primary btn-sm'><i class='fas fa-pencil-alt'></i></button></a>";
                    return html;
                }

            },
          ],
      })

});
