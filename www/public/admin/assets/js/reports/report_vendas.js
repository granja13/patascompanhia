$(document).ready(function() {

    let tabela = $("#lista_vendas").DataTable({
        "responsive": true, 
        "lengthChange": false, 
        "autoWidth": false,
        "pageLength": 25,
        ajax:{
            url: "../admin/api/json.report_vendas.php"
        },
        columns: [
            {'data': 'mes'},
            {'data': 'total'},
            {'data': 'acao'},
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

    $('#formMes').submit(function(e){
        e.preventDefault();
        $.post("../admin/report_vendas.php", $(this).serialize(), function(response){
            if(response.status === 'success'){
                $('#modalMes').modal('hide');
                tabela.ajax.reload();
                Swal.fire('Sucesso!', response.message, 'success').then(() => {
                    window.location.href = "?success=1";
                });
            } else {
                Swal.fire('Erro!', response.message, 'error');
            }
        }, 'json');
    });

});
