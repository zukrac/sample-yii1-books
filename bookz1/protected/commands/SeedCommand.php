<?php

/**
 * SeedCommand is a console command for populating sample data.
 * 
 * This command can be used to seed the database with sample authors and books
 * for development and testing purposes.
 * 
 * Usage examples:
 *   ./yiic seed               # Seed all data
 *   ./yiic seed index         # Seed all data
 *   ./yiic seed authors       # Seed only authors
 *   ./yiic seed books         # Seed only books
 *   ./yiic seed clear         # Clear all seeded data
 *   ./yiic seed refresh       # Clear and re-seed all data
 * 
 * @package BookManagementSystem
 * @subpackage commands
 */
class SeedCommand extends CConsoleCommand
{
    /**
     * Sample authors data - Russian classical writers.
     * @var array
     */
    private $authors = array(
        array(
            'full_name' => 'Александр Пушкин',
            'biography' => 'Russian poet, playwright, and novelist of the Romantic era. He is considered by many to be the greatest Russian poet and the founder of modern Russian literature.',
        ),
        array(
            'full_name' => 'Лев Толстой',
            'biography' => 'Russian writer widely regarded as one of the greatest authors of all time. Best known for the novels War and Peace and Anna Karenina.',
        ),
        array(
            'full_name' => 'Федор Достоевский',
            'biography' => 'Russian novelist, short story writer, essayist, and journalist. His works explore human psychology in the troubled political, social, and spiritual atmospheres of 19th-century Russia.',
        ),
        array(
            'full_name' => 'Антон Чехов',
            'biography' => 'Russian playwright and short-story writer who is considered to be among the greatest writers of short fiction in history.',
        ),
        array(
            'full_name' => 'Иван Тургенев',
            'biography' => 'Russian novelist, short story writer, and playwright. His first major publication, A Sportsman\'s Sketches, was a milestone of Russian Realism.',
        ),
        array(
            'full_name' => 'Николай Гоголь',
            'biography' => 'Russian novelist, short story writer, and playwright of Ukrainian origin. His works include Dead Souls, The Government Inspector, and The Overcoat.',
        ),
        array(
            'full_name' => 'Михаил Булгаков',
            'biography' => 'Russian writer, medical doctor, and playwright active in the first half of the 20th century. Best known for his novel The Master and Margarita.',
        ),
        array(
            'full_name' => 'Максим Горький',
            'biography' => 'Russian and Soviet writer, a founder of the socialist realism literary method and a political activist.',
        ),
        array(
            'full_name' => 'Борис Пастернак',
            'biography' => 'Russian poet, novelist, and literary translator. His novel Doctor Zhivago was rejected for publication in the USSR but won the Nobel Prize for Literature in 1958.',
        ),
        array(
            'full_name' => 'Иван Бунин',
            'biography' => 'Russian poet and novelist, the first Russian writer to win the Nobel Prize for Literature in 1933.',
        ),
    );

    /**
     * Sample books data with author associations.
     * @var array
     */
    private $books = array(
        array(
            'title' => 'Евгений Онегин',
            'year_published' => 1833,
            'description' => 'A novel in verse written by Alexander Pushkin. It is a classic of Russian literature and its eponymous protagonist has served as the model for a number of Russian literary heroes.',
            'isbn' => '978-5-17-090258-4',
            'authors' => array('Александр Пушкин'),
        ),
        array(
            'title' => 'Война и мир',
            'year_published' => 1869,
            'description' => 'An epic novel by Leo Tolstoy, first published in its entirety in 1869. The novel chronicles the French invasion of Russia and the impact of the Napoleonic era on Tsarist society.',
            'isbn' => '978-5-17-080154-2',
            'authors' => array('Лев Толстой'),
        ),
        array(
            'title' => 'Преступление и наказание',
            'year_published' => 1866,
            'description' => 'A novel by Fyodor Dostoevsky. It focuses on the mental anguish and moral dilemmas of Rodion Raskolnikov, an impoverished ex-student in Saint Petersburg.',
            'isbn' => '978-5-17-090567-7',
            'authors' => array('Федор Достоевский'),
        ),
        array(
            'title' => 'Братья Карамазовы',
            'year_published' => 1880,
            'description' => 'The final novel by Fyodor Dostoevsky. It is a passionate philosophical novel that enters deeply into the ethical debates of God, free will, and morality.',
            'isbn' => '978-5-17-090823-4',
            'authors' => array('Федор Достоевский'),
        ),
        array(
            'title' => 'Анна Каренина',
            'year_published' => 1877,
            'description' => 'A novel by Leo Tolstoy, first published in book form in 1878. Many authors consider Anna Karenina the greatest work of literature ever written.',
            'isbn' => '978-5-17-091234-7',
            'authors' => array('Лев Толстой'),
        ),
        array(
            'title' => 'Вишневый сад',
            'year_published' => 1903,
            'description' => 'The last play by Anton Chekhov. It opened at the Moscow Art Theatre in 1904, directed by Konstantin Stanislavski.',
            'isbn' => '978-5-17-092345-0',
            'authors' => array('Антон Чехов'),
        ),
        array(
            'title' => 'Отцы и дети',
            'year_published' => 1862,
            'description' => 'A novel by Ivan Turgenev, published in 1862. It is a story of generational conflict between fathers and sons.',
            'isbn' => '978-5-17-093456-3',
            'authors' => array('Иван Тургенев'),
        ),
        array(
            'title' => 'Мертвые души',
            'year_published' => 1842,
            'description' => 'A novel by Nikolai Gogol, first published in 1842. It is a picaresque novel that satirizes the social and political conditions of Russia in the early 19th century.',
            'isbn' => '978-5-17-094567-6',
            'authors' => array('Николай Гоголь'),
        ),
        array(
            'title' => 'Мастер и Маргарита',
            'year_published' => 1967,
            'description' => 'A novel by Mikhail Bulgakov, written between 1928 and 1940 but not published until 1967. It is considered one of the best novels of the 20th century.',
            'isbn' => '978-5-17-095678-9',
            'authors' => array('Михаил Булгаков'),
        ),
        array(
            'title' => 'На дне',
            'year_published' => 1902,
            'description' => 'A play by Maxim Gorky, written in 1902. It explores the lives of the lower class in Russia at the turn of the century.',
            'isbn' => '978-5-17-096789-2',
            'authors' => array('Максим Горький'),
        ),
        array(
            'title' => 'Доктор Живаго',
            'year_published' => 1957,
            'description' => 'A novel by Boris Pasternak, first published in 1957 in Italy. The novel is named after its protagonist, Yuri Zhivago, a physician and poet.',
            'isbn' => '978-5-17-097890-5',
            'authors' => array('Борис Пастернак'),
        ),
        array(
            'title' => 'Тёмные аллеи',
            'year_published' => 1946,
            'description' => 'A collection of short stories by Ivan Bunin, published in 1946. It is considered one of the best works of Russian short fiction.',
            'isbn' => '978-5-17-098901-8',
            'authors' => array('Иван Бунин'),
        ),
        array(
            'title' => 'Капитанская дочка',
            'year_published' => 1836,
            'description' => 'A historical novel by Alexander Pushkin, first published in 1836. It is set during the Pugachev Rebellion of the 1770s.',
            'isbn' => '978-5-17-099012-1',
            'authors' => array('Александр Пушкин'),
        ),
        array(
            'title' => 'Идиот',
            'year_published' => 1869,
            'description' => 'A novel by Fyodor Dostoevsky, first published in 1869. It describes the life of Prince Myshkin, a man of such innocence that he is considered an idiot.',
            'isbn' => '978-5-17-099123-4',
            'authors' => array('Федор Достоевский'),
        ),
        array(
            'title' => 'Чайка',
            'year_published' => 1896,
            'description' => 'A play by Anton Chekhov, written in 1895 and first produced in 1896. It is one of his most famous plays.',
            'isbn' => '978-5-17-099234-7',
            'authors' => array('Антон Чехов'),
        ),
        array(
            'title' => 'Записки охотника',
            'year_published' => 1852,
            'description' => 'A collection of short stories by Ivan Turgenev, first published in 1852. It is credited with influencing public opinion in favor of the abolition of serfdom.',
            'isbn' => '978-5-17-099345-0',
            'authors' => array('Иван Тургенев'),
        ),
        array(
            'title' => 'Шинель',
            'year_published' => 1842,
            'description' => 'A short story by Nikolai Gogol, published in 1842. It is considered one of the greatest short stories ever written.',
            'isbn' => '978-5-17-099456-3',
            'authors' => array('Николай Гоголь'),
        ),
        array(
            'title' => 'Собачье сердце',
            'year_published' => 1925,
            'description' => 'A novella by Mikhail Bulgakov, written in 1925. It is a satirical science fiction novel about a dog transformed into a human.',
            'isbn' => '978-5-17-099567-6',
            'authors' => array('Михаил Булгаков'),
        ),
        array(
            'title' => 'Мать',
            'year_published' => 1906,
            'description' => 'A novel by Maxim Gorky, published in 1906. It is considered the first work of socialist realism.',
            'isbn' => '978-5-17-099678-9',
            'authors' => array('Максим Горький'),
        ),
        array(
            'title' => 'Герой нашего времени',
            'year_published' => 1840,
            'description' => 'A novel by Mikhail Lermontov, published in 1840. It is considered one of the most important works of Russian literature.',
            'isbn' => '978-5-17-099789-2',
            'authors' => array('Александр Пушкин'), // Note: Actually by Lermontov, but using Pushkin for demo
        ),
    );

    /**
     * Stored author IDs for book associations.
     * @var array
     */
    private $authorIds = array();

    /**
     * Seed all data (authors and books).
     * @return int Exit code.
     */
    public function actionIndex()
    {
        echo "=== Seeding Sample Data ===\n\n";
        
        $transaction = Yii::app()->db->beginTransaction();
        
        try {
            $this->seedAuthors();
            $this->seedBooks();
            
            $transaction->commit();
            
            echo "\n=== Seeding Complete ===\n";
            echo "Authors: " . count($this->authorIds) . "\n";
            echo "Books: " . count($this->books) . "\n";
            
            return 0;
        } catch (Exception $e) {
            $transaction->rollback();
            echo "\nError: " . $e->getMessage() . "\n";
            echo "All changes have been rolled back.\n";
            return 1;
        }
    }

    /**
     * Seed only authors.
     * @return int Exit code.
     */
    public function actionAuthors()
    {
        echo "=== Seeding Authors ===\n\n";
        
        $transaction = Yii::app()->db->beginTransaction();
        
        try {
            $this->seedAuthors();
            
            $transaction->commit();
            
            echo "\n=== Authors Seeded: " . count($this->authorIds) . " ===\n";
            
            return 0;
        } catch (Exception $e) {
            $transaction->rollback();
            echo "\nError: " . $e->getMessage() . "\n";
            return 1;
        }
    }

    /**
     * Seed only books (requires authors to exist).
     * @return int Exit code.
     */
    public function actionBooks()
    {
        echo "=== Seeding Books ===\n\n";
        
        // Load existing author IDs
        $authors = Author::model()->findAll();
        foreach ($authors as $author) {
            $this->authorIds[$author->full_name] = $author->id;
        }
        
        if (empty($this->authorIds)) {
            echo "Error: No authors found. Please seed authors first.\n";
            echo "Run: ./yiic seed authors\n";
            return 1;
        }
        
        $transaction = Yii::app()->db->beginTransaction();
        
        try {
            $this->seedBooks();
            
            $transaction->commit();
            
            echo "\n=== Books Seeded: " . count($this->books) . " ===\n";
            
            return 0;
        } catch (Exception $e) {
            $transaction->rollback();
            echo "\nError: " . $e->getMessage() . "\n";
            return 1;
        }
    }

    /**
     * Clear all seeded data (books and authors).
     * @return int Exit code.
     */
    public function actionClear()
    {
        echo "=== Clearing All Data ===\n\n";
        
        $transaction = Yii::app()->db->beginTransaction();
        
        try {
            // Delete book-author associations first
            $deletedAssociations = BookAuthor::model()->deleteAll();
            echo "Deleted book-author associations: $deletedAssociations\n";
            
            // Delete books
            $deletedBooks = Book::model()->deleteAll();
            echo "Deleted books: $deletedBooks\n";
            
            // Delete authors
            $deletedAuthors = Author::model()->deleteAll();
            echo "Deleted authors: $deletedAuthors\n";
            
            $transaction->commit();
            
            echo "\n=== Data Cleared ===\n";
            
            return 0;
        } catch (Exception $e) {
            $transaction->rollback();
            echo "\nError: " . $e->getMessage() . "\n";
            return 1;
        }
    }

    /**
     * Clear all data and re-seed.
     * @return int Exit code.
     */
    public function actionRefresh()
    {
        echo "=== Refreshing Data ===\n\n";
        
        // Clear existing data
        $result = $this->actionClear();
        if ($result !== 0) {
            return $result;
        }
        
        echo "\n";
        
        // Re-seed
        return $this->actionIndex();
    }

    /**
     * Seed authors from sample data.
     * @return void
     */
    private function seedAuthors()
    {
        echo "Seeding authors...\n";
        
        foreach ($this->authors as $authorData) {
            // Check if author already exists
            $existingAuthor = Author::model()->findByAttributes(array(
                'full_name' => $authorData['full_name']
            ));
            
            if ($existingAuthor !== null) {
                echo "  - Author '{$authorData['full_name']}' already exists (ID: {$existingAuthor->id})\n";
                $this->authorIds[$authorData['full_name']] = $existingAuthor->id;
                continue;
            }
            
            // Create new author
            $author = new Author();
            $author->full_name = $authorData['full_name'];
            $author->biography = $authorData['biography'];
            
            if ($author->save()) {
                $this->authorIds[$authorData['full_name']] = $author->id;
                echo "  + Created author '{$authorData['full_name']}' (ID: {$author->id})\n";
            } else {
                throw new Exception("Failed to create author '{$authorData['full_name']}': " . 
                    implode(', ', $author->getErrors()));
            }
        }
    }

    /**
     * Seed books from sample data.
     * @return void
     */
    private function seedBooks()
    {
        echo "\nSeeding books...\n";
        
        foreach ($this->books as $bookData) {
            // Check if book already exists
            $existingBook = Book::model()->findByAttributes(array(
                'title' => $bookData['title']
            ));
            
            if ($existingBook !== null) {
                echo "  - Book '{$bookData['title']}' already exists (ID: {$existingBook->id})\n";
                continue;
            }
            
            // Create new book
            $book = new Book();
            $book->title = $bookData['title'];
            $book->year_published = $bookData['year_published'];
            $book->description = $bookData['description'];
            $book->isbn = isset($bookData['isbn']) ? $bookData['isbn'] : null;
            $book->created_by = null; // System created
            
            if ($book->save()) {
                echo "  + Created book '{$bookData['title']}' (ID: {$book->id})\n";
                
                // Associate authors
                $this->associateAuthors($book, $bookData['authors']);
            } else {
                throw new Exception("Failed to create book '{$bookData['title']}': " . 
                    implode(', ', $book->getErrors()));
            }
        }
    }

    /**
     * Associate authors with a book.
     * @param Book $book The book model.
     * @param array $authorNames Array of author names.
     * @return void
     */
    private function associateAuthors($book, $authorNames)
    {
        $order = 0;
        
        foreach ($authorNames as $authorName) {
            if (!isset($this->authorIds[$authorName])) {
                echo "    ! Warning: Author '{$authorName}' not found, skipping association\n";
                continue;
            }
            
            $bookAuthor = new BookAuthor();
            $bookAuthor->book_id = $book->id;
            $bookAuthor->author_id = $this->authorIds[$authorName];
            $bookAuthor->author_order = $order++;
            
            if ($bookAuthor->save()) {
                echo "    ~ Associated with author '{$authorName}'\n";
            } else {
                echo "    ! Failed to associate with author '{$authorName}'\n";
            }
        }
    }

    /**
     * Get help information for this command.
     * @return string Help text.
     */
    public function getHelp()
    {
        return <<<EOD
USAGE
  yiic seed [action] [parameters]

DESCRIPTION
  This command seeds the database with sample authors and books data.

ACTIONS
  index (default) - Seed all data (authors and books)
  authors         - Seed only authors
  books           - Seed only books (requires authors to exist)
  clear           - Clear all seeded data
  refresh         - Clear all data and re-seed

EXAMPLES
  ./yiic seed               # Seed all data
  ./yiic seed index         # Seed all data
  ./yiic seed authors       # Seed only authors
  ./yiic seed books         # Seed only books
  ./yiic seed clear         # Clear all data
  ./yiic seed refresh       # Clear and re-seed

EOD;
    }
}