// assets/js/admin.js
(function($) {
  $(document).ready(function() {
    console.log('bl-admin-js caricato');

    // --- Search Book with debounce ---
    var debounceTimer;
    $('#bl_book_search').on('input', function() {
      clearTimeout(debounceTimer);
      var raw = $(this).val().trim();
      // Se è una stringa composta solo da cifre, spazi o trattini, la puliamo
      var q = /^[\d\-\s]+$/.test(raw)
        ? raw.replace(/[^\d]/g, '')
        : raw;      
      debounceTimer = setTimeout(function() {
        if (q.length < 3) {
          $('#bl_search_results').empty();
          return;
        }
        $.getJSON(bl_admin_ajax.ajax_url, {
          action: 'bl_search_books',
          nonce:  bl_admin_ajax.nonce,
          q:      q
        })
        .done(function(response) {
          var container = $('#bl_search_results').empty();
          if (!response.success || !response.data.length) {
            container.append('<p class="bl-no-results">' + bl_admin_ajax.no_results + '</p>');
            return;
          }
          var list = $('<ul class="bl-search-list"></ul>');
          response.data.forEach(function(item) {
            // Crea il <li> con attributi data-*
            var li = $('<li>').addClass('bl-search-item')
              .attr('data-isbn', item.isbn)
              .attr('data-title', item.title)
              .attr('data-authors', item.authors)
              .attr('data-publisher', item.publisher)
              .attr('data-year', item.year)
              .attr('data-cover', item.cover || '');

            // Anteprima copertina
            if (item.cover) {
              li.append(
                $('<img>').addClass('bl-search-cover')
                  .attr('src', item.cover)
                  .attr('alt', item.title)
              );
            }

            // Info testuale
            li.append(
              $('<div>').addClass('bl-search-info')
                .text(item.title + ' — ' + item.authors + ' — ' + item.publisher + ' (' + item.year + ')')
            );

            list.append(li);
          });
          container.append(list);
        })
        .fail(function(err) {
          console.error('Search AJAX error', err);
        });
      }, 500);
    });

    // --- Populate fields when selecting a search result ---
    $(document).on('click', '.bl-search-list li', function() {
      var $li       = $(this);
      var isbn      = $li.data('isbn');
      var title     = $li.data('title');
      var authors   = $li.data('authors');
      var publisher = $li.data('publisher');
      var year      = $li.data('year');
      var cover     = $li.data('cover');

      $('#bl_book_search').val(title);
      $('#bl_isbn').val(isbn);
      $('#bl_author').val(authors);
      $('#bl_publisher').val(publisher);
      $('#bl_year').val(year);
      $('#bl_cover').val(cover);
      $('#bl_search_results').empty();
    });

    // --- Fetch ASIN via PAAPI ---
    $(document).on('click', '#bl_fetch_asin', function(e) {
      e.preventDefault();
      var rawIsbn = $('#bl_isbn').val().trim();
      // Rimuovi tutto ciò che non è cifra
      var isbn = rawIsbn.replace(/[^\d]/g, '');      
      if (!isbn) {
        alert(bl_admin_ajax.invalid_isbn);
        return;
      }
      $.getJSON(bl_admin_ajax.ajax_url, {
        action: 'bl_fetch_asin',
        nonce:  bl_admin_ajax.nonce,
        isbn:   isbn
      })
      .done(function(response) {
        if (response.success && response.data.asin) {
          $('#bl_asin').val(response.data.asin);
        } else {
          var msg = response.data && response.data.error_message
            ? response.data.error_message
            : bl_admin_ajax.error_fetch_asin;
          alert(msg);
        }
      })
      .fail(function() {
        alert(bl_admin_ajax.error_fetch_asin);
      });
    });

  });
})(jQuery);
