<!DOCTYPE html>
<html lang="en" >
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Postfix Virtual Mail Domains Manager</title>

    <!-- Bootstrap core CSS -->
    <link href="css/app.css" rel="stylesheet">

    <style type="text/css">
  		body {
  		  padding-top: 50px;
  		}
  		.starter-template {
  		  padding: 40px 15px;
  		  text-align: center;
  		}

      table.table tr.selected {
        background-color: yellow;
      }

      table.table th {
        text-align: center;
        font-size: 1.1em;
      }

    </style>


    <script type="x-tmpl-mustache" id="template-table">


<table class="table table-condensed table-bordered table-hover">
  <tr id="head-model">
   <%#fields%>
    <th><%field%></th>
   <%/fields%>
  </tr>
  <%#rows%>
  <tr id="row-model">
    <%#values%>
      <td><%value%></td>
    <%/values%>
  </tr>
  <%/rows%>
</table>


    </script>


  </head>

  <body>
  <div id="app">
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Postfix Virtual Domains Admin</a>
        </div>

        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav nav-tabs" role="tablist">
            <li class="active"><a data-toggle="tab" href="#domains">Domains</a></li>
            <li><a data-toggle="tab" href="#users">Users</a></li>
            <li><a data-toggle="tab" href="#transport">Transport</a></li>
            <li><a data-toggle="tab" href="#forwardings">Forwardings</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      
      </div>

    </nav>

    <div class="container">
    <div class="tab-content starter-template">

    <div role="tabpanel" class="tab-pane active" id="domains">
      <div class="row">
        <div class="col-md-8">
          <table class="table table-condensed table-bordered table-hover">
            <tr>
            <th>domain</th>
            </tr>
            @foreach($domains as $domain)
              <tr>
              <td>{{$domain->domain}}</td>
              </tr>
            @endforeach

          </table>
        </div>
      </div>
      </div>  <!-- tabpanel #domains -->


    <div role="tabpanel" class="tab-pane" id="users">
      <div class="row">
        <div class="col-md-8">
          <table class="table table-condensed table-bordered table-hover">
            <tr>
            <th>email</th>
            <th>password</th>
            <th>quota</th>
            </tr>
            @foreach($users as $user)
              <tr>
              <td>{{$user->email}}</td>
              <td>{{$user->password}}</td>
              <td>{{$user->quota}}</td>
              </tr>
            @endforeach

          </table>
        </div>
      </div>
      </div>  <!-- tabpanel #users -->

    <div role="tabpanel" class="tab-pane" id="forwardings">
      <div class="row">
        <div class="col-md-8">
          <table class="table table-condensed table-bordered table-hover">
            <tr>
            <th>source</th>
            <th>destination</th>
            </tr>
            @foreach($forwardings as $forwarding)
              <tr>
              <td>{{$forwarding->source}}</td>
              <td>{{$forwarding->destination}}</td>
              </tr>
            @endforeach

          </table>
        </div>
      </div>
      </div>  <!-- tabpanel #forwardings -->


    <div role="tabpanel" class="tab-pane" id="transport">
      <div class="row">
        <div class="col-md-8">
          <table class="table table-condensed table-bordered table-hover">
            <tr>
            <th>domain</th>
            <th>transport</th>
            </tr>
            @foreach($transports as $transport)
              <tr>
              <td>{{$transport->domain}}</td>
              <td>{{$transport->transport}}</td>
              </tr>
            @endforeach

          </table>
        </div>
      </div>
      </div>  <!-- tabpanel #transports -->

    <a id="update_table" class="btn btn-primary btn-info">Refresh table</a>

    <a id="add_row" class="btn btn-primary ">Add Row</a>
    <a id='delete_row' class="btn btn-default ">Delete Row</a>
    <a id='save_row' class="btn btn-warning" >Save Row</a>




    </div> <!-- tab-content -->
    </div><!-- /.container -->
    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
{{--     <script type="text/javascript"  src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
     <script type="text/javascript">window.jQuery || document.write('<script src="../../assets/js/vendor/jquery.min.js"><\/script>')</script>
--}}
    <script type="text/javascript" src="js/app.js"></script>
    <script type="text/javascript" src="js/all.js"></script>


    <script type="text/javascript">


      Mustache.tags = ["<%","%>"];

      var $table = null;
      var last_row_id = null;
      var selected_entity = null;
      var $head_names = null;

      function DelayedFunction(obj,_function) {
        this.obj = obj;
        this.call = function () {
          _function(this.obj);
        }
      }

     function postpone(delayedFunction) {
        window.setTimeout (delayedFunction.call,10);
      }

      function loadTemplate() {
        var template = $('#template-table').html();
        Mustache.parse(template);   // optional, speeds up future uses
        return template; 
      }

      function setNewTabPaneVars() {
          $table = $('table:visible');
          active_tab = $('.active a');
          console.log('active tab:',active_tab);
          $head_row = $table.find('tr').first();
          $head_names = $head_row.find('th').map(function(i,th) { return $(th).text() });
          console.log('=>head_names',$head_names);
      };


      setNewTabPaneVars();

    $(document).ready(function() {

      if ($table == undefined) {
        correctGlobals();
      }


      refreshGlobals();

      url = window.location.href;

      $("[href='"+ window.location.hash +"'").trigger('click');
      
      setInterval(refreshGlobals,500);

      function correctGlobals() {

          $table = $('table:visible');

          if ( last_row_id == undefined || last_row_id.toString() == 'NaN' ) {
              last_row_id = $table.find('tr td').length;
              $table.attr('last_row_id',last_row_id);
          }

      }

      function refreshGlobals() {

  //      console.log('$table',$table);
        last_row_id =  parseInt($table.attr('last_row_id'));

        selected_entity = $('.active a').attr('href').substring(1).trim();
 //       console.log('existing last_row_id:',last_row_id);
        if ( last_row_id.toString() == 'NaN' || last_row_id == 0) {
          $('#save_row').hide();
        } else {
          $('#save_row').show();
          if (last_row_id > 1) {
            $('a#save_row').text('Save rows')
          } else {
            $('a#save_row').text('Save row')
          }
        }
      };


      // when changing tab (called after some timeout has passed)
      $('.nav a').click(  function(evt) {
        a_elem = this;
        postpone(new DelayedFunction (a_elem,function () {
          correctGlobals();
          tab_name = $(this.a_elem).attr('href'); 
          window.location.hash = tab_name ;
          $table = $('table:visible');
          console.log('this.a_elem',this.a_elem);
          console.log('changed to tab: ' + tab_name.substring(0) ); 
          $head_row = $table.find('tr').first();
          $head_names = $head_row.find('th').map(function(i,th) { return $(th).text() });
          console.log('=>head_names : ',$head_names);
          last_row_id = $table.attr('last_row_id');
          console.log('new last_row_id: ', last_row_id);
       }));
     });

      // actions over rows - click, double click and  pressing keys
      $(document).on('click','table tr',{},function(evt) {
        console.log('CLICKED on: ',evt.target);
        if ($(evt.target).parents('tr').hasClass('selected')) {
          $('.selected').removeClass('selected');
        } else {
          $('.selected').removeClass('selected');
          $(evt.target).parents('tr').addClass('selected');
        }
        console.log('selected: ',$('.selected').text());
      })

      $(document).on('dblclick','table tr',{},function(event) {
        event.preventDefault();
        console.log("DOUBLE clicked on ", event.target);
        $(event.target).parents('tr').children('td').each(
          function(i,td) {
            val = $(td).text();
            $(td).html("<INPUT type='text' name='"+ $head_names[i] +"' value='"+val+"'/>");
        });
      });

      $(document).on('keyup','table tr',{},function(event) {
        event.preventDefault();
        key = event.originalEvent.code;
       
        console.log('tr key PRESSED: ',key);
        console.log('on ',event.target);
        
        if ( key == 'Enter') {
          $row_inputs = $(event.target).parents('tr').find('input');
          updated_row = new Object();
          $row_inputs.each(function(i,input) {
            key = $(input).attr('name');
            val = $(input).val();
            updated_row[ key ] = val;
          });

          console.log('updated row: ',updated_row);

          isNew = $(event.target).parents('tr').attr('new') ;

          if (isNew == '1') {
            console.log('New record! Pressing add row button!');
            $('#save_row').trigger('click'); 
          } else {
          $.get('/update_row',
            {entity: selected_entity, row: updated_row},
            function(resp) {
              console.log('response to updated row: ',resp);
              revertRowEditing();
            });
          }
        } 

      });

      $('table').on('keyup','input[type=text]',function(evt) {
        key = evt.originalEvent.code;
        console.log('INPUT key: ',key );
        console.log('ON: ',evt.target);
        input = evt.target;
        if (key == 'Escape') {
          console.log('Cancelling editing row!');
          revertRowEditing(input);
        }
      });

      function revertRowEditing(input) {
        console.log('reverting edit');
        $parent_tr = $(input).parents('tr');
        console.log('parent table row: ',$parent_tr,$parent_tr.hasClass('new_row'));
        if ($parent_tr.hasClass('new_row')) {
          console.log('deleting new_row!');
          $parent_tr.remove();
        } else {
          $parent_tr.find('input').replaceWith(function(i) { return $(this).val() });
        }
      }

      // actions related to the buttons

      // refresh the table using a fragment template
      $('#update_table').click(function(evt) {
        console.log('UPDATE table!');
        $.get('/get',
          {entity:selected_entity},
          function(data) {
            console.log('data',data);
            tmpl = loadTemplate();
            tableHtml = Mustache.render(tmpl,data);
            console.log(tableHtml);
            $table.replaceWith(tableHtml);
          })
      });

      // what to do when clicking in 'ADD ROW'
      $('#add_row').click(function(evt) {

        evt.preventDefault();

        console.log('last_row_id = ' + last_row_id);

        
        $new_row = null;

        if (last_row_id != 0) {
          $last_row = $table.find('tr').last();
          $new_row = $last_row.clone();
        } else {
          console.log("No last row found! Creating a new one...");
          new_cols = $table.find('th').length;
          $new_row = $('<tr>');
          for (i=0; i < new_cols; i++) {
            $new_row.append('<td>&nbsp</td>');
          }
        }

        $new_row.attr('new','1');

        console.log('new_row',$new_row);

        $new_row.find('td').each(function(i,td) {
          console.log('each',i,td);
          textContent = $(td).text();
          name = $head_names.get(i);
          $(td).html("<input type='text' name='" + name + "' value=''>");
        });

        $new_row.addClass('new_row');
        last_row_id ++;
        $new_row.attr( 'id','row' + last_row_id );
        $table.append( $new_row );
        console.log( 'last row_id' ,last_row_id );
        $table.attr('last_row_id',new String(last_row_id));

      });

      $('#delete_row').click( function(evt) {
        evt.preventDefault();
        console.log('deleting row!');
        num_rows = $table.find('tr').length - 1;
        if (num_rows <= 1) { 
          console.log( "Can't delete because there is just one row!");
         return }
        $sel_tr = $table.find('.selected');

        console.log('selected tr:',$sel_tr);

        sel_row = new Object();
        $sel_tr.find('td').each(function(i,td) {
          key = $head_names.get(i);
          value = $(td).text();
          sel_row[key] = value  ; 
         }); 

        console.log('send:',sel_row);

        $.get('/delete_row',
          {entity: selected_entity, row: sel_row },
          function(json) {
            console.log('response to delete:',json);
          }
          );
       // window.location.reload();
      });

      $('#save_row').click( function(evt) {
        evt.preventDefault();
        console.log('save row!');
        $last_tr = $table.find('tr[id=row'+ last_row_id + ']');
        console.log('last tr found',$last_tr);
        new_row = new Object();
        $last_tr.find('input').each(function(i,input) {
          key = $(input).attr('name');
          value = $(input).val();
          new_row[key] = value  ; 
         }); 
        console.log('send:',new_row);
        $.get(
          '/add_row',
           {entity: selected_entity, row: new_row },
            function (json) {
              console.log('add row response');
              console.log(json);
              if (json.hasOwnProperty('error')) {
                $last_tr.css('background-color','red');
              }
              $last_tr.find('input').each(function(i,input) {
                  $(input).replaceWith( $(this).val() );
               } );
            });
      });


    });
    </script>

  </body>
</html>
