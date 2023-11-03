<?php
class Todo {
    public function __construct(
        public string $todo,
        public string $category,
        public bool $done = false,
        public ?int $id = null,
        public ?PDO $db = null,
    ) {}

    public static function find( int $id, PDO $db ): Todo|null {
        $stmt = $db->prepare( "SELECT * FROM todos WHERE id = :id LIMIT 1" );
        $stmt->execute( [
            ":id" => $id,
        ] );

        $todo = $stmt->fetch( PDO::FETCH_ASSOC );

        if( ! $todo ) {
            return null;
        }

        return self::from_array( $todo, $db );
    }

    public static function from_array( array $todo, ?PDO $db = null ) {
        return new Todo(
            id: $todo["id"],
            todo: $todo["todo"],
            category: $todo["category"],
            done: $todo["done"],
            db: $db,
        );
    }

    public function render() {
        $done_class = $this->done ? "done" : "";
        $done_text = $this->done ? "Undone" : "Done";
        ?>
        <div class="todo <?php echo $done_class; ?>">
            <div class="todo-name"><?php echo htmlspecialchars( $this->todo ); ?></div>
            <button hx-post="/api.php?action=mark_done&id=<?php echo $this->id; ?>&done=<?php echo intval( !$this->done ); ?>" hx-target="closest .todo" hx-swap="outerHTML"><?php echo $done_text; ?></button>
        </div>
        <?php
    }

    public function save() {
        $stmt = $this->db->prepare( "INSERT INTO todos (todo, category) VALUES (:todo, :category)" );
        $stmt->execute( [
            ":todo" => $this->todo,
            ":category" => $this->category,
        ] );

        $this->id = $this->db->lastInsertId();
    }

    public function mark_done( bool $done ) {
        $stmt = $this->db->prepare( "UPDATE todos SET done = :done WHERE id = :id LIMIT 1" );
        $stmt->execute( [
            ":id" => $this->id,
            ":done" => intval( $done ),
        ] );

        $this->done = $done;
    }
}