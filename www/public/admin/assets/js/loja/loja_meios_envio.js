$(document).ready(function() {

    $("#metodos_envio").DataTable({
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "pageLength": 25,
        "buttons": ["copy", "excel", "pdf", "print"],
        ajax:{
            url: "../admin/api/json.metodos_envio.php"
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
            {
                'data': 'logotipo',
                'class': "text-center",
                'render': function (data, type, row, meta) {
                    return '<img src="../assets/imagens/icones/' + data + '" alt="Metodo Envio" width="100" height="100">';
                }
            },
            {
                'data': null,
                'class': "text-center",
                'render': function (data, type, row, meta) {
                    let html = "<a href='loja_meios_envio_edita.php?id=" + row.id + "' ><button class='btn btn-primary'><i class='fas fa-pencil-alt'></i></button></a>";
                    html += "<a href='#' data-del='loja_meios_envio_delete.php?id=" + row.id + "' style='margin-left: 10px;' class='apaga'><button class='btn btn-danger'><i class='fas fa-times'></i></button></a>";
                    return html;
                }

            },
          ],
      })

      $('#metodos_envio').on('click', 'a.apaga', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Confirmar? Escreve "apaga" na caixa!',
            input: 'text',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'OK',
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.value !== undefined && result.value === 'apaga') {
                location.href = $(this).attr('data-del');
            }
        });

    });

});
