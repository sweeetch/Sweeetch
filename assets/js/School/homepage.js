/** school profil js and css */

// css
import "../../css/school/homepage.scss";

// JQuery 
import $ from 'jquery';

global.$ = $;
global.jQuery = $; 

// bootstrap 
import 'bootstrap';
import 'admin-lte';

// assets > js > components 
import '../components/button_disabled.js'; // disable button when form is submitted 
import '../components/edit.js'; // edit sections 
import '../components/file.js'; // file name 
import '../components/message.js'; // success or error message 
import '../components/table.js';  // dataTable 

// tinymce 
tinymce.init({
    selector: 'textarea'
});

// datepicker 
$(document).ready(function() {
    $('.js-datepicker').datepicker({
        format: 'dd-mm-yyyy'
    });
});

// dataTables
$(function () {   
    $("#dash1").DataTable({
        "paging": true,
        "pageLength": 5,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        "responsive": true,
        "language": {
        "emptyTable": "Aucun candidat pour l'instant",
        "paginate": {
            "previous": "Précédent",
            'next':'Suivant'
            }
        }
    });   

    $("#dash2").DataTable({
        "paging": true,
        "pageLength": 5,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        "responsive": true,
        "language": {
        "emptyTable": "Aucun recrutement en cours",
        "paginate": {
            "previous": "Précédent",
            'next':'Suivant'
            }
        }
    });
    
    $("#dash3").DataTable({
        "paging": true,
        "pageLength": 5,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        "responsive": true,
        "language": {
        "emptyTable": "Aucune attente d'acceptation",
        "paginate": {
            "previous": "Précédent",
            'next':'Suivant'
            }
        }
    });

    $("#dash4").DataTable({
        "paging": true,
        "pageLength": 5,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        "responsive": true,
        "language": {
        "emptyTable": "Aucune recrutement terminé",
        "paginate": {
            "previous": "Précédent",
            'next':'Suivant'
            }
        }
    });

    $("#dash5").DataTable({
        "paging": true,
        "pageLength": 5,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        "responsive": true,
        "language": {
        "emptyTable": "Aucune recrutement terminé",
        "paginate": {
            "previous": "Précédent",
            'next':'Suivant'
            }
        }
    });
});
