(function($){
    $(document).ready(function(){
        new Init()
    });

    /*////////////////////////////*/

    function Init()
    {
        var progressFormManager  = new ProgressFormManager;
        var quickEditManager     = new QuickEditManager;
        var orderDateManager     = new OrderDateManager;
    }

    function ProgressFormManager()
    {
        var $this = this;
    
        $this.elements = {
            addProgressButton: $('.wte-add-progress'),
            progressItemsContainer: $('.wte-progress-items'),
            dataInput: $('.wte_progress_data'),
            submitButton: $('.wte-settings #submit')
        }

        $this.__construct = function()
        {
            $this.loadItems();

            $this.elements.addProgressButton.on('click', $this.handleAddProgress);
            $this.elements.progressItemsContainer.on('click', '.wte-delete-item', $this.handleRemoveProgress);

            $this.elements.submitButton.on('click', $this.handleSubmitClick);
        }

        $this.loadItems = function()
        {
            var data;

            try {
                data = JSON.parse($this.elements.dataInput.val());
            } catch (e) {
                data = {
                    items: []
                }
            }

            data.items.forEach(function(item){
                $this.addNewProgress(item);
            });
        }
    
        $this.addNewProgress = function(data)
        {
            var p = new ProgressForm(data);

            p.render($this.elements.progressItemsContainer);
        }

        $this.handleAddProgress = function(event)
        {
            event.preventDefault();

            $this.addNewProgress();
        }

        $this.handleRemoveProgress = function(event)
        {
            event.preventDefault();

            if (window.confirm('Borrar este estado?')) {
                $(event.target).closest('.wte-progress-item').remove();
            }
        }

        $this.handleSubmitClick = function(event)
        {
            try {
                var data = {
                    items: $this.unloadItems()
                };


                $this.elements.dataInput.val(JSON.stringify(data));
            } catch (error) {
                alert('Ha habido un error.');
            }
        }

        $this.unloadItems = function()
        {
            var itemsData = [];

            $this.elements.progressItemsContainer.children('.wte-progress-item').each(function(){
               var progressItemElement = $(this);

                itemsData.push({
                    label: progressItemElement.find('.wte_progress_label').attr('value'),
                    percentage: progressItemElement.find('.wte_progress_percentage').attr('value'),
                    description: progressItemElement.find('.wte_progress_description').attr('value')
                });
            });

            return itemsData;
        }

        $this.__construct();
    }

    function ProgressForm(data)
    {
        var $this = this;

        $this.__construct = function()
        {
            $this.data = data || {};
        }

        $this.render = function(target)
        {
            target.append(
                '<div class="wte-form-group wte-progress-item">'+
                    '<div class="wte-form-titulo">'+
                        '<input type="text" name="wte_progress_label" class="wte_progress_label" placeholder="Nombre" value="'+($this.data.label || '')+'">'+
                        '<input type="number" name="wte_progress_percentage" class="wte_progress_percentage" placeholder="Porcentaje" value="'+($this.data.percentage || '')+'">'+
                    '</div>'+
                    '<textarea name="wte_progress_description" class="wte_progress_description" cols="30" rows="3" placeholder="Descripción">'+
                        ($this.data.description || '')+
                    '</textarea>'+
                    '<a href="#" class="wte-delete-item" style="color: #a00">Move to Trash</a>'+
                '</div>'
            );
        }

        $this.__construct();
    }

    function QuickEditManager()
    {
        var $this = this;
    
        $this.__construct = function()
        {
            $( '#the-list' ).on( 'click', '.editinline', function(){
                setTimeout($this.handleQuickEditOpen.bind(this), 50)
            });
        }

        $this.handleQuickEditOpen = function(event)
        {
            var post_id = $( this ).closest( 'tr' ).attr( 'id' ) || '';

            post_id = post_id.replace('post-', '');

            $this.setFields(post_id);
        }

        $this.setFields = function(postId)
        {
            var dataElement = $('#wte-data-'+postId);

            $( 'select#wte_range_unit', '.inline-edit-row' ).val(
                dataElement.find('div[data-name="wte_range_unit"]').attr('data-value')
            );
            $( 'input#wte_range_min', '.inline-edit-row' ).val(
                dataElement.find('div[data-name="wte_range_min"]').attr('data-value')
            );
            $( 'input#wte_range_max', '.inline-edit-row' ).val(
                dataElement.find('div[data-name="wte_range_max"]').attr('data-value')
            );
        }

        $this.__construct();
    }

    function OrderDateManager()
    {
        var $this = this;
        $this.elements = {
            buttons: {}
        };

        $this.__construct = function()
        {
            $this.registerElements();
            $this.registerEvents();
        }

        $this.registerElements = function()
        {
            $this.elements.buttons.restart = $('#woo-restart-order-date');
            $this.elements.buttons.restore = $('#woo-restore-order-date');

            $this.elements.buttons.removeETA = $('#wte-remove-custom-eta-data');
        }

        $this.registerEvents = function()
        {
            $this.elements.buttons.restart.click($this.handleButtonClick('restart'));
            $this.elements.buttons.restore.click($this.handleButtonClick('restore'));
            $this.elements.buttons.removeETA.click($this.removeETA);
        }

        $this.handleButtonClick = function(action)
        {
            return function(event) {
                event.preventDefault();

                var button = $(this);

                $this.setState('active', button);

                $.post({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wte_handle_order_date',
                        'wte-order-id': $("#post_ID").val(),
                        'wte-order-action': action
                    },
                    success: function(){ window.location.reload(true); $this.setState('idle');},
                    error: function(response) {
                        alert('Al parecer ha habido un error: '+response.responseText);
                        console.log('error', response);
                        $this.setState('idle')
                    },
                })
            }
        }

        $this.removeETA = function(event)
        {
            event.preventDefault();

            var button = $(this);

            $this.setState('active', button);

            $.post({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'wte_handle_remove_eta_data',
                    'wte-order-id': $("#post_ID").val(),
                },
                success: function(){ window.location.reload(true); $this.setState('idle');},
                error: function(response) {
                    alert('Al parecer ha habido un error: '+response.responseText);
                    console.log('error', response);
                    $this.setState('idle')
                },
            })
        }

        $this.setState = function(state, button)
        {
            if (state === 'active') {
                $this.onActive(button);
            } else if (state === 'idle') {
                $this.onIdle(button);
            }
        }

        $this.onActive = function(button)
        {
            button.text(button.attr('data-text-active'));
        }

        $this.onIdle = function(button)
        {
            button.text(button.attr('data-text-idle'));
        }
    
        $this.__construct();
    }

})(jQuery);