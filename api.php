<?php
require( "autoload.php" );

$db = new PDO("mysql:host=localhost;dbname=todoapp", "todo", "todo");

switch( $_GET['action'] ) {
    case "add_todo":
        if( empty( $_POST['todo'] ) ) {
            ?>
            <div id="message" hx-swap-oob="true">
                <div class="warning">Todo is missing!</div>
            </div>
            <?php
            exit;
        }

        if( empty( $_POST['category'] ) ) {
            ?>
            <div id="message" hx-swap-oob="true">
                <div class="warning">Category is missing!</div>
            </div>
            <?php
            exit;
        }

        header( "HX-Trigger: todo_count, todo_added, todo_added_" . md5( $_POST['category'] ) );

        $todo = new Todo(
            todo: $_POST['todo'],
            category: $_POST['category'],
            db: $db,
        );

        $todo->save();

        ?>
        <div id="message" hx-swap-oob="true">
            <div class="success">Todo added!</div>
        </div>
        <?php

        break;

    case "todo_list":
        if( ! empty( $_GET['category'] ) ) {
            $stmt = $db->prepare( "SELECT * FROM todos WHERE category = :category" );
            $stmt->execute( [
                ":category" => $_GET["category"],
            ] );
            $category_id = "_".md5( $_GET["category"] );
        } else {
            $stmt = $db->query( "SELECT * FROM todos" );
            $category_id = "";
        }

        $todos = $stmt->fetchAll( PDO::FETCH_ASSOC );

        ?><div class="todo-list" hx-get="/api.php?action=todo_list&category=<?php echo urlencode( $_GET['category'] ); ?>" hx-swap="outerHTML" hx-trigger="todo_added<?php echo $category_id; ?> from:body"><?php

        foreach( $todos as $todo ) {
            Todo::from_array( $todo )->render();
        }

        ?></div><?php

        break;

    case "mark_done":
        $todo = Todo::find( $_GET['id'], $db );

        header( "HX-Trigger: done_count, done_count_" . md5( $todo->category ) );

        if( ! $todo ) {
            // handle errors
        } else {
            $todo->mark_done( (bool) $_GET['done'] );
        }

        $todo->render();

        break;

    case "completed_count":
        $stmt = $db->query( "SELECT COUNT(*) FROM todos WHERE done = 1" );
        echo $stmt->fetchColumn();
        break;

    case "total_count":
        $stmt = $db->query( "SELECT COUNT(*) FROM todos" );
        echo $stmt->fetchColumn();
        break;

    case "category_list":
        $stmt = $db->query( "SELECT DISTINCT category FROM todos" );
        $categories = $stmt->fetchAll( PDO::FETCH_COLUMN );

        foreach( $categories as $category ) {
            ?>
            <a href="#" hx-get="/api.php?action=todo_list&category=<?php echo urlencode( $category ); ?>" hx-target=".todo-list" hx-swap="outerHTML" class="category">
                <?php echo htmlspecialchars( $category ); ?> <span class="count" hx-get="/api.php?action=category_incomplete_count&category=<?php echo urlencode( $category ); ?>" hx-trigger="load, done_count_<?php echo md5( $category ); ?> from:body" hx-swap="innerHTML" hx-target="this"></span>
            </a>
            <?php
        }
        break;

    case "category_incomplete_count":
        $stmt = $db->prepare( "SELECT COUNT(*) FROM todos WHERE done = 0 AND category = :category" );
        $stmt->execute( [
            ":category" => $_GET["category"],
        ] );
        echo $stmt->fetchColumn();
        break;

    case "todo_form":
        ?>
        <form id="todoform" hx-post="/api.php?action=add_todo" hx-target=".todo-list" hx-swap="beforeend" autocomplete="off">
            <div class="row">
                <input type="text" placeholder="Add todo..." name="todo" class="todo-name">
                <button>Add todo</button>
            </div>
            <select name="category" class="category-select">
                <option value="">-- Select category --</option>
                <option>General</option>
                <option>House</option>
                <option>Car</option>
                <option>Shopping</option>
            </select>
        </form>
        <?php
        break;
}
