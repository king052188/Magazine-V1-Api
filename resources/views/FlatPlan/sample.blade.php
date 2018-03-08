<!DOCTYPE html>
<html lang="en">
<?php
        $year = IsSet($_GET["year"]) ? $_GET["year"] : "";
        $issue = IsSet($_GET["issue"]) ? $_GET["issue"] : "";
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Flat Plan - Under Development</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link href="{{ asset('css/kpa.work.style.css', false) }}" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.1/jquery-ui.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!-- Styles -->
    <style>
        #sortable-list		{ padding:0; }
        #sortable-list li	{ padding:4px 8px; color:#000; cursor:move; list-style:none; width:500px; background:#ddd; margin:10px 0; border:1px solid #999; }
        #message-box		{ background:#fffea1; border:2px solid #fc0; padding:4px 8px; margin:0 0 14px 0; width:500px; }

        .modal { color: #000; }
        .modal .modal-body p { padding: 0; margin: 0; }
        p.ad_title { margin: 2px 0 0 4px; z-index: -1; text-align: left;  width: 100%; }
        p.ad_title a { text-decoration: none; color: #fff; font-size: .7em; border-bottom: 1px solid #999; padding-bottom: 3px; }
        p.ad_title a:hover { font-size: 1em; font-weight: 600;}
    </style>
</head>
<body>
<div class="flex-center position-ref full-height" style="display: none;">
        <div class="links">
            <div id="message-box"> Waiting for sortation submission...</div>
            <form id="dd-form" action="/kpa/work-v2/flat-planning/go" method="GET">
                <p>
                    <input type="checkbox" value="1" name="autoSubmit" id="autoSubmit" checked="checked" />
                    <label for="autoSubmit">Automatically submit on drop event</label>
                </p>
                <ul id="sortable-list">
                    <?php $order = array(); ?>
                    @for($i = 0; $i < COUNT($flats); $i++)
                        <li title='{{ $flats[$i]->Id }}'> {{ $flats[$i]->Id}} </li>
                        <?php $order[] = $flats[$i]->Id; ?>
                    @endfor
                </ul>
                <br />
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="sort_order" id="sort_order" value="<?php echo implode(',',$order); ?>" />
                <input type="submit" name="do_submit" value="Submit Sortation" class="button" />
            </form>
        </div>
    </div>
</div>

<script>
    var ads_count = {{ COUNT($ad_lists) }};
    var ref_number = "{{ $reference["number"] }}";
    var magazine_id = {{ $mag_info[0]->Id }};
    var magazine = "{{ $mag_info[0]->magazine_name }}";
    var created = {{ $created["channel"] }};
    var placeholder_drag_id = 0;

    function on_show_info(id) {
        $(document).ready( function() {
            $('#btnModal').click();
            var magazine = document.getElementById("drag_" + id).getAttribute("data-magazine");
            var client = document.getElementById("drag_" + id).getAttribute("data-company");
            var size = document.getElementById("drag_" + id).getAttribute("data-size");
            var page = document.getElementById("drag_" + id).getAttribute("data-page");
            var sizes = size.split("_");
            var html = "<p>Publication: <b>"+magazine+"</b></p>";
            html += "<p>Client: <b>"+client+"</b></p>";
            html += "<p>Size: <b>"+sizes[0] + "/" + sizes[1] + " " + sizes[2] +"</b></p>";
            html += "<p>Page#: <b>"+page+"</b></p>";
            $( "#modal_info" ).empty().prepend(html);
        } )
    }

    $( function() {
        $( "#draggable" ).draggable();
    } );

    function activate_draging(id) {
        $(document).ready(function() {
            $( "#"+id ).draggable();
        })
    }

    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        var data_id = ev.target.id;
        ev.dataTransfer.setData("text", data_id);
    }

    function drop(ev, page_id) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");

        console.log("data: " + data);
        console.log("page_id: " + page_id);

        var magazine = document.getElementById(data).getAttribute("data-magazine");
        var client = document.getElementById(data).getAttribute("data-company");
        var size = document.getElementById(data).getAttribute("data-size");
        var position = document.getElementById(page_id).getAttribute("data-position");
        var color_code = "#288437";

        if (page_id === undefined || page_id === null) {
            page_id = "reset";
        }

        var pages = page_id.split("_");
        var page_number = parseInt(pages[1]);
        var ab = null;

        if(magazine != "PLACEHOLDER") {
          remove(data);
          color_code = "#288437";
        }
        else {
          data = data + placeholder_drag_id;
          placeholder_drag_id++;
          color_code = "#af1354";
        }

        console.log("page_number: " + data);
        console.log("placeholder_drag_id: " + placeholder_drag_id);

        if(size == "FULL_DOUBLE_SPREED") {
            var sort = 0;
            if(position == "LEFT") {
                sort = 1;
                ab = "A";
            }
            else {
                sort = 2;
                ab = "B";
            }

            // first
            var html_ = get_innerHTML(page_id);
            html_ += pre_append(data, size, magazine, client, page_number, ab);
            do_append(page_id, html_);

            save(ref_number, client, page_id, data, size, sort);

            if(position == "LEFT") {
                page_number++;
                ab = "B";
            }
            else {
                page_number--;
                ab = "A";
            }

            // second
            page_id = pages[0] + "_" + page_number;
            html_ = get_innerHTML(page_id);
            html_ += pre_append(data, size, magazine, client, page_number, ab);
            do_append(page_id, html_);
        }
        else if(size == "1_2_DOUBLE_SPREED") {
            var sort = 0;
            if(position == "LEFT") {
                sort = 1;
                ab = "A";
            }
            else {
                sort = 2;
                ab = "B";
            }

            // first
            var html_ = get_innerHTML(page_id);
            html_ += pre_append(data, size, magazine, client, page_number, ab);
            do_append(page_id, html_);

            save(ref_number, client, page_id, data, size, sort);

            if(position == "LEFT") {
                page_number++;
                ab = "B";
            }
            else {
                page_number--;
                ab = "A";
            }

            // second
            page_id = pages[0] + "_" + page_number;
            html_ = get_innerHTML(page_id);
            html_ += pre_append(data, size, magazine, client, page_number, ab);
            do_append(page_id, html_);
        }
        else {
            var html_ = get_innerHTML(page_id);
            html_ += pre_append(data, size, magazine, client, page_number, ab, color_code);
            do_append(page_id, html_);
            save(ref_number, client, page_id, data, size, 0);
        }
    }

    function save(ref_number, client, page_id, data, size, sort) {
        var client_parse = client.replace(" ", "%20");
        var params = "flat_ref=" + ref_number + "&company=" + client_parse + "&page=" + page_id + "&placeholder=" + data + "&size=" + size + "&sort=" + sort;
        do_save(params);
        get_placeholder();
    }

    function do_save(url_data) {
        $(document).ready(function() {
          $.ajax({
            beforeSend: function() {
                console.log("Updating the sort order in the database.'")
            },
            complete: function(result) {
                console.log(result);
                console.log("Database has been updated.'")
            },
            data: url_data,
            type: 'get',
            url: '/kpa/work-v2/flat-plan/save'
          });
        })
    }

    get_placeholder();
    function get_placeholder() {
        $(document).ready(function() {
            $.ajax({
                beforeSend: function() {
                  $("#placeholder_lists").empty().prepend("***");
                },
                complete: function(result) {
                  $("#placeholder_lists").empty().prepend(result.responseText);
                },
                data: "",
                type: 'get',
                url: '/kpa/work-v2/flat-plan/get-placeholder'
            });
        })
    }

    //placeholder_lists

    function pre_append(id, size, magazine, client, page_number, double_spreed, color) {
        var html;

        var id_s = id.split("_");

        console.log(id_s);

        var acronym  = magazine.charAt(0).toUpperCase() + client.charAt(0).toUpperCase();

        if(double_spreed != null) {
            acronym = acronym + " - " + double_spreed;
        }

        if(client == "PLACEHOLDER") {
          acronym = "PH";
        }

        console.log(magazine);

        switch (size) {
            case "1_8_HORIZONTAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_8_HORIZONTAL' data-xy='53:27' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 53px; height: 27px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_8_VERTICAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_8_VERTICAL' data-xy='27:53' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 27px; height: 53px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_6_HORIZONTAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_6_HORIZONTAL' data-xy='70:33' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 70px; height: 33px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_6_VERTICAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_6_VERTICAL' data-xy='33:70' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 33px; height: 70px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_4_HORIZONTAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_4_HORIZONTAL' data-xy='59:26.5' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 59px; height: 26.5px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_4_VERTICAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_4_VERTICAL' data-xy='26.5:59' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 26.5px; height: 59px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_4_BANNER":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_4_BANNER' data-xy='109:26.533' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 109px; height: 26.533px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_3_SQUARE":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_3_SQUARE' data-xy='53:69' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 53px; height: 69px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_3_HORIZONTAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_3_HORIZONTAL' data-xy='109:37' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 109px; height: 37px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_3_VERTICAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_3_VERTICAL' data-xy='37:124' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left;  margin: 2px 0 0 2px; width: 37px; height: 124px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_2_HORIZONTAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_2_HORIZONTAL' data-xy='109:59' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 109px; height: 59px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_2_LONG_VERTICAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_2_LONG_VERTICAL' data-xy='53:124' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left;  margin: 2px 0 0 2px; width: 53px; height: 124px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_2_VERTICAL_ISLAND":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_2_VERTICAL_ISLAND' data-xy='53:85' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left;  margin: 2px 0 0 2px; width: 53px; height: 85px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "1_2_DOUBLE_SPREED":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='1_2_DOUBLE_SPREED' data-xy='53:85' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 109px; height: 59px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "2_3_VERTICAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='2_3_VERTICAL' data-xy='70:124' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left;  margin: 2px 0 0 2px; width: 70px; height: 124px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "2_3_HORIZONTAL":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='2_3_HORIZONTAL' data-xy='109:84' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; float: left; margin: 2px 0 0 2px; width: 109px; height: 84px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "FULL_DOUBLE_SPREED":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='DOUBLE_SPREED' data-xy='112:127.3' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; margin: -2px 0 0 0.3px; width: 112px; height: 127.3px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            case "FULL_BLEEDS":
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='FULL_BLEEDS' data-xy='112:127.3' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; margin: -2px 0 0 0.3px; width: 112px; height: 127.3px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;

            default:
                html = "<div id='"+id+"' data-company='"+client+"' data-magazine='"+magazine+"' data-size='FULL' data-xy='109:124' data-page='"+page_number+"' draggable='true' ondragstart='drag(event)' style='background: "+color+"; margin: 2px 0 0 2px; width: 109px; height: 124px;'>";
                html += "<a href='#' onclick='remove_x("+id_s[1]+")' style='float: right; position: relative; z-index: 9;'> <img src='http://icons.iconarchive.com/icons/graphicloads/100-flat-2/24/close-icon.png' /> </a>";
                html += "<p class='ad_title'><a href='#' onclick='on_show_info("+id_s[1]+")'>"+ acronym +"</a></div>";
                break;
        }
        return html;
    }

    function do_append(id, value) {
        $(document).ready(function() {
            $( "#"+id ).empty().prepend(value);
        })
    }

    function get_innerHTML(id) {
        var html = null;
        $(document).ready(function() {
            html = $( "#"+id ).html();
        })
        return html;
    }

    function remove(id) {
        $(document).ready(function() {
            $( "#"+id ).remove();
        })
    }

    function remove_x(id) {
      $(document).ready(function() {
          $.ajax({
            url: "/kpa/work-v2/flat-plan/del/" + id,
            dataType: "text",
            beforeSend: function() {
                $( "#drag_"+id ).text("Please wait...");
            }
          }).done(function(json_data) {
            console.log(json_data);
            alert("Remove was done.");
            $( "#drag_"+id ).remove();
            location.reload();
          });
      })
    }
</script>

<div class="main">

    <div class="right-pane">

        <div class="title">
            <h3>List of Ads</h3>
        </div>

        <div class="ads_container">
            <div class="wrapper" id="images_list_A" ondrop="drop(event)" ondragover="allowDrop(event)">

                <div class="page">
                    <ul>
                        @for($i = 0; $i < COUNT($ad_lists); $i++)
                            <li id="drag_{{ $ad_lists[$i]->Id }}" data-company="{{ $ad_lists[$i]->client_company }}" data-magazine="{{ $ad_lists[$i]->mag_name }}" data-size="{{ $ad_lists[$i]->package_size }}" draggable="true" ondragstart="drag(event)" >
                                {{ $ad_lists[$i]->client_company }} <span> {{ " [ " . $ad_lists[$i]->package_name . " ]" }}</span>
                            </li>
                        @endfor
                    </ul>
                </div>

            </div>
        </div>

        <div class="title">
            <h3>Shape / Placeholder</h3>
        </div>

        <div class="ads_container">
            <div class="wrapper" id="placeholder_lists" ondrop="drop(event)" ondragover="allowDrop(event)">

            </div>
        </div>
    </div>

    <div class="left-pane">
        <div class="pages_container" >
            <div class="flat_info">
                <h3><a href="http://ckt.kpa.ph:5000/dashboard">HOME</a> | {{ $mag_info[0]->magazine_name }} - {{ $reference["number"] }} </h3>
                @if($flat_plan != null)
                    <p> Year: {{ $flat_plan[0]->magazine_year }} and issue: {{ $flat_plan[0]->magazine_issue }} </p>
                @endif
            </div>
            <div id="loading" class="wrapper" style="display: block;">
                <h1 id="loading_text" style="text-align: center; margin-top: 300px;"></h1>
            </div>
            <div id="flat" class="wrapper" style="display: none;">
                <div class="page">
                    <div class="page_partner">
                        <div class="page-border page-margins" id="page_back_cover" data-position="LEFT" ondrop="drop(event, 'page_back_cover')" ondragover="allowDrop(event)"></div>
                        <p class="page-title" style="text-align: center;">Back Cover</p>
                    </div>

                    <div class="page_partner">
                        <div class="page-border page-margins" id="page_front_cover" data-position="RIGHT" ondrop="drop(event, 'page_front_cover')" ondragover="allowDrop(event)"></div>
                        <p class="page-title" style="text-align: center;">Front Cover</p>
                    </div>
                </div>

                <div class="page">
                    <div class="page_partner">
                        <div class="page-border page-margins" id="page_front_inside" data-position="LEFT" ondrop="drop(event, 'page_front_inside')" ondragover="allowDrop(event)"></div>
                        <p class="page-title" style="text-align: center;">Front Inside</p>
                    </div>

                    <div class="page_partner">
                        <div class="page-border page-margins" id="page_back_inside" data-position="RIGHT" ondrop="drop(event, 'page_back_inside')" ondragover="allowDrop(event)"></div>
                        <p class="page-title" style="text-align: center;">Back Inside</p>
                    </div>
                </div>

                <div class="page_single">
                    <div class="page_partner">
                        <div class="page-border page-margins" id="page_1" ondrop="drop(event, 'page_1')" ondragover="allowDrop(event)"></div>
                        <p class="page-title" style="text-align: center;">Page #1</p>
                    </div>
                </div>

                <?php
                    for ($i = 2; $i <= 27; $i++) {
                ?>

                <div class="page">
                    <div class="page_partner">
                        <div class="page-border page-margins" id="page_<?php echo $i; ?>" data-position="LEFT" ondrop="drop(event, '<?php echo "page_". $i; ?>')" ondragover="allowDrop(event)"></div>
                        <p class="page-title" style="text-align: center;">Page #<?php echo $i; ?></p>
                    </div>

                    <?php $i++; ?>

                    <div class="page_partner">
                        <div class="page-border page-margins" id="page_<?php echo $i; ?>" data-position="RIGHT" ondrop="drop(event, '<?php echo "page_". $i; ?>')" ondragover="allowDrop(event)"></div>
                        <p class="page-title" style="text-align: center;">Page #<?php echo $i; ?></p>
                    </div>
                </div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>

</div>

<!-- Trigger the modal with a button -->
<a href="#" id="btnModal" data-toggle="modal" data-target="#myModal" data-backdrop="static" data-keyboard="false"></a>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 id="modal_title_info" class="modal-title">Ad Information</h4>
            </div>
            <div id="modal_info" class="modal-body">
                <p>Some text in the modal.</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnYes" class="btn btn-primary" style="display: none;">Yes</button>
                <button type="button" id="btnNo" class="btn btn-default" style="display: none;">No</button>
                <button type="button" id="btnClose" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    init();

    function init() {
      if(created == 0) {
          do_alert();
          $("#loading_text").text("=== NO DATA ===");
          $("#loading").show();
      }
      else {
        populate_flat_plan();
      }
    }

    $(document).ready( function() {
        $("#btnYes").click(function () {
            var year = "<?php echo $year; ?>";
            var issue = "<?php echo $issue; ?>";
            var params = "mid=" + magazine_id + "&mag_name=" + magazine + "&tans_num=" + ref_number + "&year=" + year + "&issue=" + issue;
            do_create_channel(params, "btnYes");
        });
        $("#btnNo").click(function () {
            window.location.href="http://localhost:5000/dashboard";
        });
    } )

    function populate_flat_plan() {
        $(document).ready( function() {
            $.ajax({
                url: "/kpa/work-v2/get-flat-plan/" + ref_number,
                dataType: "text",
                beforeSend: function () {
                    $("#flat").hide();
                    $("#loading_text").text("Please wait...");
                    $("#loading").show();
                },
                success: function(plans) {
                    var json = $.parseJSON(plans);
                    if(json == null)
                        return false;
                    console.log(json);
                    if(json.code == 404) {
                        alert("Oops, " + json.message );
                        return false;
                    }
                    console.log(json.data);
                    $(json.data).each(function(key, name){

                      if(name.company_name != "PLACEHOLDER") {
                        remove(name.placeholder_id);
                        color_code = "#288437";
                      }
                      else {
                        color_code = "#af1354";
                      }

                      var page_id = name.page_id;

                      var pages = page_id.split("_");

                      var page_number = pages[1];

                      var size = name.placeholder_size;

                      var position = name.sort_order;

                      var ab = null;

                      if(size == "FULL_DOUBLE_SPREED") {
                        if(position == 1) {
                            ab = "A";
                        }
                        else {
                            ab = "B";
                        }

                        console.log(page_number);
                        // first
                        var html_ = get_innerHTML(page_id);
                        html_ += pre_append(name.placeholder_id, name.placeholder_size, name.mag_name, name.company_name, page_number, ab, color_code);
                        do_append(page_id, html_);

                        if(position == 1) {
                            page_number++;
                            ab = "B";
                        }
                        else {
                            page_number--;
                            ab = "A";
                        }

                        console.log(page_number);

                        // second
                        page_id = pages[0] + "_" + page_number;
                        html_ = get_innerHTML(page_id);
                        html_ += pre_append(name.placeholder_id, name.placeholder_size, name.mag_name, name.company_name, page_number, ab, color_code);
                        do_append(page_id, html_);
                      }
                      else if(size == "1_2_DOUBLE_SPREED") {
                        if(position == 1) {
                            ab = "A";
                        }
                        else {
                            ab = "B";
                        }

                        // first
                        var html_ = get_innerHTML(page_id);
                        html_ += pre_append(name.placeholder_id, name.placeholder_size, name.mag_name, name.company_name, page_number, ab, color_code);
                        do_append(page_id, html_);

                        if(position == 1) {
                            page_number++;
                            ab = "B";
                        }
                        else {
                            page_number--;
                            ab = "A";
                        }
                        // second
                        page_id = pages[0] + "_" + page_number;
                        html_ = get_innerHTML(page_id);
                        html_ += pre_append(name.placeholder_id, name.placeholder_size, name.mag_name, name.company_name, page_number, ab, color_code);
                        do_append(page_id, html_);
                      }
                      else {
                        var html_ = get_innerHTML(page_id);
                        html_ += pre_append(name.placeholder_id, name.placeholder_size, name.mag_name, name.company_name, page_number, ab, color_code);
                        do_append(page_id, html_);
                      }

                    });
                    $("#flat").show();
                    $("#loading").hide();
                }
            });
        } )
    }

    function do_alert() {
        $('#btnModal').click();
        $('#btnClose').hide();
        var html = null;
        if(ads_count == 0) {
            $('#btnNo').text("OK, Back to Dashboard");
            $('#btnNo').show();
            $( "#modal_title_info" ).text("Warning");
            html = "<div style='text-align: center;'><p>Magazine <b>'"+magazine+"'</b> no ads available.</p>";
        }
        else {
            $('#btnYes').show();
            $('#btnNo').show();
            $( "#modal_title_info" ).text("Confirming");
            html = "<div style='text-align: center;'><p>Would you like to create a new </p>";
            html += "<p>FLAT PLANING for magazine <b>'"+magazine+"'</b></p>";
            html += "<p>with reference#: <b>"+ref_number+"</b></p></div>";
        }
        $( "#modal_info" ).empty().prepend(html);
    }

    function do_create_channel(url_data, button_id) {
        $(document).ready(function() {
            $.ajax({
                beforeSend: function() {
                    $('#'+button_id).text("Please wait...");
                },
                success: function(data) {
                    var json = $.parseJSON(data);
                    if(json.code == 200) {
                        window.location.href="/kpa/work-v2/flat-plan/" + magazine_id + "/" + ref_number;
                        return false;
                    }
                    alert("Oops, something went wrong.");
                    $('#'+button_id).text("Yes");
                    console.log(json);
                },
                data: url_data,
                type: 'get',
                dataType: "text",
                url: '/kpa/work-v2/flat-plan/create-channel'
            });
        })
    }
</script>
</body>
</html>
