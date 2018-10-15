<html>
<head>
    <title>tables generate</title>
    <script src="http://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
</head>
<body>
    <div class="form">
        <div class="fields">
            <label for="cols">Укажите количество столбцов:</label>
            <input id="cols" class="inp" type="text" />
        </div>
        <div class="fields">
            <label for="rows">Укажите количество cтрок:</label>
            <input id="rows" class="inp" type="text" />
        </div>
        <div class="fields">
            <input type="button" class="btn set" value="Задать" />
            <input type="button" class="add" value="+" />
        </div>
    </div>
    <div class="table"></div>

</body>
</html>

<script>
    (function(){
        $('.set').on('click',function(){
            var  cols = $('#cols').val();
            var  rows = $('#rows').val();
            $('.table').html(generation(cols,rows));

            getJson();
        });

        $('.add').on('click',function(){
            var  cols = $('#cols').val();
            var  rows = $('#rows').val();
           $('.table').appendTo(addrow(cols));
        });

        $('div').on('click','.dlt',function(){
           sel = $(this).parent('td').parent('tr');
            deleterow(sel);
        });

    var generation = function(col,row){
        var $number=1;
        table = '<table cellpadding="5">';
        for (var i=0; i<row; i++){
            table += '<tr class="row_'+i+'">';
            if (i === 0){
                for(var a=0; a<col; a++){
                    table += '<th>name_'+a+'</th>';
                }
            }else{
                for (var j=0; j<col; j++){
                    table += '<td class="cols_'+j+'"><input type="text" class="item_'+$number+'"></td>';
                    $number++;
                }
                table += '<td class="cols_'+j+'"><div class="dlt">x</div></td>';
            }
            table += '</tr>';
        }
        table += '<table>';

        return table;
    };

    var addrow = function(col){
        tbody = $('.table').find('tbody');
        var tr = $('<tr>',{
            class: 'rows',
        }).appendTo(tbody);

        for (var j=0; j<col; j++){
            td = $('<td>',{
                class: 'cols',
            }).appendTo(tr);
            $('<input>',{
                type:'text',
                class:'item_'
            }).appendTo(td);
        }
        td = $('<td>',{}).appendTo(tr);
        $('<div>',{
            class:'dlt',
            text:'x',

        }).appendTo(td);
    };

    var deleterow = function(cls){
            $(cls).remove();
        };

    var getJson = function(){
        var myRows = [];
        var $headers = $("th");
        var $rows = $("tbody tr").each(function(index) {
            $cells = $(this).find("td");
            myRows[index] = {};
            $cells.each(function(cellIndex) {
                myRows[index][$($headers[cellIndex]).html()] = $(this).html();
            });
        });
        console.log(myRows);
        var myObj = {};
        myObj.myrows = myRows;

        console.log(JSON.stringify(myObj));
    }
    })();
</script>


<style>
    .fields{
        width: 350px;
        text-align: right;
        margin-bottom: 15px;
    }
    .fields .inp{
        width: 40px;
        height: 25px;
    }
    .dlt{
        font-weight: bold;
        font-size: 14px;
        cursor: pointer;
    }
    td{
        width:30px;
        text-align: center;
        border: 1px solid rgba(128, 128, 128, 0.5);
    }

    th{
        background: rgba(128, 128, 128, 0.5);
    }
</style>

