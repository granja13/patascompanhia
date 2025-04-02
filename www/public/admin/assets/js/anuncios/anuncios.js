$(document).ready(function() {

    $("#lista_anuncios").DataTable({
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "pageLength": 25,
        "buttons": ["copy", "excel", "pdf", "print"],
        ajax:{
            url: "../admin/api/json.anuncios.php"
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
            {
                'data': 'img',
                'class': "text-center",
                'render': function (data, type, row, meta) {
                    return '<img src="../assets/imagens/anuncios/' + data + '" alt="Produto" width="100" height="100">';
                }
            },
            {'data': 'tipo'},
            {'data': 'nome'},
            {'data': 'link'}
          ],
      })

});
