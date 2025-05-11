# Book Library

**Plugin WordPress** per gestire e visualizzare la libreria di libri letti dal blogger, con ricerca su Google Books e link affiliati Amazon.

## Caratteristiche

- CPT **Libro** con campi: ISBN, Autore, Editore, Anno, Copertina, ASIN, Raccomandato.
- Metabox AJAX per import dati da Google Books + fetch ASIN via PAAPI.
- Shortcode `[book_library]` per front-end suddiviso in sezioni.
- Internazionalizzazione (.pot + it_IT).

## Installazione

1. Carica la cartella `book-library-plugin` in `/wp-content/plugins/`.  
2. Attiva il plugin da **Plugin > Attivi**.  
3. Vai in **Impostazioni > Book Library** e inserisci:
   - Google Books API Key  
   - Amazon PAAPI Access Key, Secret Key, Associate Tag  
   - Limite ricerche/minuto  

## Uso

- Aggiungi un nuovo “Libro” da **Libri > Aggiungi nuovo**  
- Inserisci titolo o ISBN, seleziona il risultato e salva.  
- Usa lo shortcode `[book_library]` in una pagina per mostrare la libreria.


# Book Library

**WordPress Plugin** to manage and display the blogger’s reading list, with Google Books lookup and Amazon Affiliate links.

## Features

- **Custom Post Type** “Book” with fields:
  - ISBN  
  - Author  
  - Publisher  
  - Publication Year  
  - Cover Image URL  
  - ASIN (fetched via Amazon PAAPI)  
  - Recommended (checkbox)  
- **AJAX-powered Metabox** to search Google Books by title or ISBN, fetch extended details (including publisher via selfLink) and populate all fields.  
- **“Fetch ASIN”** button to automatically retrieve the Amazon ASIN via Product Advertising API.  
- **Shortcode** `[book_library]` to render:
  - “Currently Reading” section (1–2 columns, cover + details)  
  - “Just Finished” section (full width list, cover on hover)  
  - Additional categories (2-column layout)  
- **Affiliate links** for each title (uses stored ASIN, falls back to ISBN) with your Associate Tag.  
- Fully **internationalized** (English default + `.pot` file for other languages).

## Installation

1. Upload the folder `book-library-plugin/` to your `/wp-content/plugins/` directory.  
2. Activate the plugin via “Plugins” in WordPress admin.  
3. Go to **Settings → Book Library** and configure:
   - **Google Books API Key**  
   - **Amazon PAAPI Access Key**  
   - **Amazon PAAPI Secret Key**  
   - **Amazon Associate Tag**  
   - **Searches per minute** limit  

## Usage

1. In the admin menu select **Books → Add New**.  
2. In the “Book Details” metabox start typing a title or ISBN (with or without dashes/spaces).  
3. Click the correct result to auto-fill all fields (including Title, Author, Publisher, Year, Cover, ISBN).  
4. (Optional) Click **Fetch ASIN** to retrieve the Amazon ASIN automatically.  
5. Choose a category, mark as “Recommended” if desired, and publish.  
6. In any page or post insert the shortcode:

   ```html
   [book_library]
