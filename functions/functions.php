<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'oceanwp-woo-mini-cart','font-awesome','simple-line-icons','oceanwp-style','oceanwp-woocommerce','oceanwp-woo-star-font' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION
// 
// Forçar status "Concluído" após confirmação do pagamento do pedido.
/*
add_action( 'woocommerce_payment_complete', 'alterar_status_para_concluido_automaticamente' );

function alterar_status_para_concluido_automaticamente( $order_id ) {
    if ( !$order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );

    // Verifica se o pedido ainda não está concluído
    if ( $order->get_status() !== 'completed' ) {
        $order->update_status( 'completed', 'Pedido alterado automaticamente para concluído.' );
    }
}*/


//teste
/*
function custom_pmpro_login_redirect( $redirect_to, $request, $user ) {
    if ( !is_wp_error( $user ) && isset( $_REQUEST['redirect_to'] ) && strpos( $_REQUEST['redirect_to'], 'checkout' ) !== false ) {
        return $_REQUEST['redirect_to'];
    }
    return wc_get_checkout_url(); // Redireciona para o checkout do WooCommerce caso não haja um redirect específico
}
add_filter( 'login_redirect', 'custom_pmpro_login_redirect', 10, 3 );
*/

//Criando nova função para Assinantes Inativos
add_action('init', 'registrar_funcao_assinante_inativo');
function registrar_funcao_assinante_inativo() {
    add_role('assinante_inativo', 'Assinante Inativo', [
        'read' => true,
        // Nenhuma permissão adicional
    ]);
}


// Hook para restringir a página 1974 com base na função de usuário

add_action('template_redirect', 'restrict_access_to_page');

function restrict_access_to_page() {
    if (is_page(1974)) {
        $redirect_page_id = 5737; // ID da página para redirecionamento

        if (!is_user_logged_in()) {
            // Redireciona usuário não logado para a página de login
            wp_redirect(get_permalink($redirect_page_id));
            exit;
        }

        $user = wp_get_current_user();
        if (!in_array('subscriber', $user->roles)) {
            // Redireciona usuários que não são "Assinantes" para a página 5737
            wp_redirect(get_permalink($redirect_page_id));
            exit;
        }
    }
}

// Adiciona a pergunta e campos de presente no checkout (suporta múltiplos e-mails)
add_action('woocommerce_after_order_notes', 'add_gift_selection_and_multiple_emails', 100);
function add_gift_selection_and_multiple_emails($checkout) {
    $cart_items = WC()->cart->get_cart();
    $jornada_id = [4916, 4918]; // ou [4916, 4918] se quiser permitir múltiplos produtos

    $count = 0;
    foreach ($cart_items as $item_key => $item) {
        $product_id = $item['product_id'];
        if (in_array($product_id, [4916, 4918])) {
            for ($i = 1; $i <= $item['quantity']; $i++) {
				$count++;
				echo '<div class="gift-choice-block">';
				echo '<div class="gift-header-wrapper">';
echo '<strong>Unidade ' . $i . '</strong>';
echo '<span class="gift-tooltip-icon" tabindex="0" aria-label="Você pode usar essa jornada para você ou presentear alguém. O e-mail do presenteado pode ser informado agora ou depois na aba Presenteados no seu perfil.">ℹ️</span>';
echo '</div>';



				if ($count === 1) {
					echo "<label><input type='radio' name='gift_option_$count' class='gift-radio-self' value='self' checked> 						Usar para mim mesmo 🎓</label><br>";
					echo "<label><input type='radio' name='gift_option_$count' class='gift-radio-present' value='present'> 							Presentear alguém 🎁</label>";
				} else {
					echo "<label><input type='radio' name='gift_option_$count' class='gift-radio-present' value='present' 							checked style='display:none;'></label>";
					echo "<p>🎁 Presentear alguém</p>";
				}


				echo "<div class='gift-email-field' style='display:none; margin-top:10px;'>";
				woocommerce_form_field("gift_recipient_email_$count", array(
					'type' => 'email',
					'label' => "E-mail do presenteado $count",
					'required' => false,
				), $checkout->get_value("gift_recipient_email_$count"));
				echo "</div>";
				echo '</div>';
			}

        }
    }
}


/*
// Script para alternar a visibilidade do campo de e-mail corretamente
add_action('woocommerce_after_checkout_form', 'gift_email_toggle_script');
function gift_email_toggle_script() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        function toggleGiftFields() {
            var giftOption = $('input[name="gift_option"]:checked').val();
            if (giftOption === 'present') {
                $('#gift_emails_container').slideDown();
            } else {
                $('#gift_emails_container').slideUp();
                $('[id^="gift_recipient_email_"]').val('');
            }
			
			atualizarConfirmacaoCheckout();
        }
        $('input[name="gift_option"]').change(toggleGiftFields);
        toggleGiftFields();
    });
		// Exibir mensagem de confirmação dinâmica abaixo do botão de compra
		function atualizarConfirmacaoCheckout() {
			var giftOption = $('input[name="gift_option"]:checked').val();
			var mensagem = '';

			if (giftOption === 'self') {
				mensagem = '<div id="gift-confirmation-message" style="margin-top:15px; padding:10px; background:#f0f8ff; border-left:4px solid #5D1973;"><strong>✔️ Esta jornada será vinculada ao seu perfil.</strong> Você poderá acessá-la ao finalizar a compra.</div>';
			} else {
				mensagem = '<div id="gift-confirmation-message" style="margin-top:15px; padding:10px; background:#fff3cd; border-left:4px solid #ffc107;"><strong>🎁 Esta jornada será enviada como presente.</strong> Somente o presenteado terá acesso ao conteúdo.</div>';
			}

			$('#gift-confirmation-message').remove();
			$('.woocommerce-checkout-review-order-table').after(mensagem);
		}

    </script>
    <?php
}
*/

//Dividir entre "Usar para mim mesmo" e "Presentear"
add_action('woocommerce_after_checkout_form', 'add_self_selection_toggle');
function add_self_selection_toggle() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var cartQuantity = <?php echo WC()->cart->get_cart_contents_count(); ?>;
            var fieldWrapper = $('#gift_emails_container');

            if (fieldWrapper.length > 0) {
                for (var i = 1; i <= cartQuantity; i++) {
                    var checkbox = $('<input type="checkbox" class="gift-self-check" id="gift_self_' + i + '" name="gift_self_' + i + '" />');
                    var label = $('<label for="gift_self_' + i + '"> Usar para mim</label>');
                    var wrapper = $('<div style="margin-bottom:10px;"></div>');

                    var inputField = $('#gift_recipient_email_' + i).closest('.form-row');
                    wrapper.append(inputField.clone()).append(checkbox).append(label);
                    inputField.replaceWith(wrapper);
                }
            }
        });
    </script>
    <?php
}

add_action('woocommerce_after_checkout_form', 'fix_email_toggle_per_unit');
function fix_email_toggle_per_unit() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            function toggleEmailFields() {
                $('.gift-choice-block').each(function(index) {
                    var block = $(this);
                    var presentRadio = block.find('.gift-radio-present');
                    var selfRadio = block.find('.gift-radio-self');
                    var emailField = block.find('.gift-email-field');

                    if (presentRadio.length && presentRadio.is(':checked')) {
                        emailField.slideDown();
                    } else {
                        emailField.hide().find('input').val('');
                    }
                });
            }

            // Gatilho ao mudar opção
            $(document).on('change', '.gift-radio-present, .gift-radio-self', function() {
                toggleEmailFields();
            });

            // Gatilho ao carregar a página
            toggleEmailFields();
        });
    </script>
    <?php
}




/* Script para exibir/esconder o campo de e-mail corretamente
add_action('woocommerce_after_checkout_form', 'update_gift_option_script');
function update_gift_option_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            function toggleGiftField() {
                var giftOption = $('input[name="gift_option"]:checked').val();
                var emailContainer = $('#gift_recipient_email_container');

                if (giftOption === 'present') {
                    emailContainer.show();
                } else {
                    emailContainer.hide();
                    $('#gift_recipient_email').val(''); // Limpa o campo ao ocultá-lo
                }
            }

            $('input[name="gift_option"]').change(toggleGiftField);
            toggleGiftField(); // Chama a função ao carregar a página
        });
    </script>
    <?php
}
*/


// Salva o campo adicional no pedido
add_action('woocommerce_checkout_update_order_meta', 'save_multiple_gift_emails');
function save_multiple_gift_emails($order_id) {
    $i = 1;
    $self_count = 0;

    while (isset($_POST["gift_option_$i"])) {
        $option = sanitize_text_field($_POST["gift_option_$i"]);
        $email = isset($_POST["gift_recipient_email_$i"]) ? sanitize_email($_POST["gift_recipient_email_$i"]) : '';

        if ($option === 'self') {
            $self_count++;
        } elseif ($option === 'present') {
            if (is_email($email)) {
                add_post_meta($order_id, 'gift_recipient_email_list', $email);
            }
        }

        $i++;
    }

    if ($self_count > 0) {
        update_post_meta($order_id, 'gift_self_count', $self_count);
    }

    update_post_meta($order_id, 'gift_option', 'mixed');
}





// Hook para processar a compra e definir a função de usuário corretamente
add_action('woocommerce_payment_complete', 'sync_woocommerce_user_roles_with_multiple_gifts', 10, 1);
function sync_woocommerce_user_roles_with_multiple_gifts($order_id) {
    $order = wc_get_order($order_id);
    $buyer_user_id = $order->get_user_id();
    $gift_option = get_post_meta($order_id, 'gift_option', true);
    $gift_emails = get_post_meta($order_id, 'gift_recipient_email_list');
    $gift_self_count = intval(get_post_meta($order_id, 'gift_self_count', true));

    if (in_array($gift_option, ['present', 'mixed'])) {
        // Lógica para presenteados
        if (!empty($gift_emails)) {
            foreach ($gift_emails as $email) {
                $user = get_user_by('email', $email);
                if (!$user) {
                    $password = wp_generate_password();
                    $user_id = wp_create_user($email, $password, $email);
                    if (!is_wp_error($user_id)) {
                        set_user_role_to_subscriber($user_id); // apenas assinante
                        send_gift_email($email, $password);
                    }
                } else {
                    $user = new WP_User($user->ID);
                    $user->set_role('subscriber'); // sobrescreve função caso já exista
                }
            }
        }

        // Lógica para o comprador
        if ($gift_self_count > 0) {
            add_subscriber_to_customer($buyer_user_id); // mantém cliente e adiciona assinante
        } else {
            set_user_role_to_customer($buyer_user_id); // define cliente (mantendo assinante se tiver)
        }
    } else {
        add_subscriber_to_customer($buyer_user_id);
    }
}



// Função para definir a função "Assinante" corretamente ao presentado
function set_user_role_to_subscriber($user_id) {
    $user = new WP_User($user_id);

    // Para presenteados, queremos que tenham SOMENTE a função 'subscriber'
    $user->set_role('subscriber');

    error_log("Função 'Assinante' definida como única para o usuário ID $user_id.");
}

function add_subscriber_to_customer($user_id) {
    $user = new WP_User($user_id);

    // Adiciona 'subscriber' apenas se ainda não tiver
    if (!in_array('subscriber', $user->roles)) {
        $user->add_role('subscriber');
        error_log("Função 'Assinante' adicionada ao usuário ID $user_id.");
    }

    // Garante que mantenha também a função de cliente
    if (!in_array('customer', $user->roles)) {
        $user->add_role('customer');
        error_log("Função 'Cliente' adicionada ao usuário ID $user_id.");
    }
}




// Função para definir a função "Cliente" corretamente sem remover "Assinante"
function set_user_role_to_customer($user_id) {
    $user = new WP_User($user_id);

    // Se o usuário já tem a função "Assinante", mantém ambas
    if (in_array('subscriber', $user->roles)) {
        $user->add_role('customer'); // Adiciona "Cliente", mas não remove "Assinante"
        error_log("Função 'Cliente' adicionada ao usuário ID $user_id sem remover 'Assinante'.");
    } else {
        $user->set_role('customer'); // Se não for assinante, define como cliente
        error_log("Função 'Cliente' atribuída ao usuário ID $user_id.");
    }
}

// Valida se o email do beneficiário foi preenchido quando a opção "Presentear alguém" for escolhida
add_action('woocommerce_checkout_process', 'validate_unique_and_valid_gift_emails');
function validate_unique_and_valid_gift_emails() {
    $emails = [];

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'gift_recipient_email_') !== false && !empty($value)) {
            $email = sanitize_email($value);
            if (!is_email($email)) {
                wc_add_notice(__('⚠️ Um dos e-mails inseridos está inválido. Corrija ou deixe o campo em branco.', 'woocommerce'), 'error');
            } elseif (in_array($email, $emails)) {
                wc_add_notice(__('⚠️ O mesmo e-mail foi utilizado mais de uma vez. Cada presente deve ter um e-mail diferente.', 'woocommerce'), 'error');
            } else {
                $emails[] = $email;
            }
        }
    }
}




/* Esta sendo comentada no dia 15/04 para testes
// Adiciona a validação em JavaScript no checkout
add_action('woocommerce_after_checkout_form', 'add_gift_recipient_email_validation_script');
function add_gift_recipient_email_validation_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            function validateGiftEmail() {
                var giftOption = $('input[name="gift_option"]:checked').val();
                var emailField = $('#gift_recipient_email');
                var emailError = $('#gift_recipient_email_error');

                if (giftOption === 'present' && emailField.val().trim() === '') {
                    emailError.text('⚠️ Insira o e-mail do beneficiário antes de finalizar a compra.');
                    emailField.css('border', '2px solid red');
                } else {
                    emailError.text('');
                    emailField.css('border', '');
                }
            }

            $('input[name="gift_option"]').change(validateGiftEmail);
            $('#gift_recipient_email').on('input', validateGiftEmail);
        });
    </script>
    <?php
}
*/

// Função para enviar e-mail ao beneficiário com mensagem personalizada
function send_gift_email($recipient_email, $password = '', $personal_message = '') {
    $reset_password_url = wp_lostpassword_url();
    $login_url = get_permalink(2261); // Altere se necessário

    $subject = "🎁 Sua Jornada está esperando por você!";

    $message = '
    <html>
    <body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f9f9f9;">
        <table align="center" width="100%" style="max-width:600px; margin:auto; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
            <tr>
                <td style="padding:20px 30px;">
                    <h2 style="color:#5d1973; margin-bottom:10px;">Olá!</h2>
                    <p style="font-size:16px; color:#333;">Você recebeu acesso à <strong>Jornada de Finanças Pessoais</strong> da Kukka! </p>';

    if (!empty($personal_message)) {
        $message .= '
                    <div style="margin-top:20px; padding:15px; background:#f4f0fa; border-left:4px solid #5d1973;">
                        <p style="margin:0; font-style:italic; color:#333;"><strong>Mensagem de quem te presenteou:</strong><br>' . nl2br(esc_html($personal_message)) . '</p>
                    </div>';
    }

    if (!empty($password)) {
        $message .= '
                    <div style="margin-top:20px; padding:15px; background:#f0f8ff; border-left:4px solid #3B9B71;">
                        <p style="margin:0; color:#333;">
                            Sua conta foi criada!<br>
                            <strong>E-mail:</strong> ' . esc_html($recipient_email) . '<br>
                            <strong>Senha:</strong> ' . esc_html($password) . '
                        </p>
                    </div>';
    }

    $message .= '
                    <div style="margin-top:30px;">
                        <a href="' . esc_url($login_url) . '" style="display:inline-block; background:#5d1973; color:white; padding:12px 20px; border-radius:4px; text-decoration:none; font-weight:bold;">Acessar Jornada</a>
                    </div>

                    <p style="margin-top:20px; font-size:14px; color:#777;">
                        Se preferir, redefina sua senha: 
                        <a href="' . esc_url($reset_password_url) . '" style="color:#5d1973;"> Criar nova senha</a>
                    </p>

                    <p style="font-size:13px; color:#bbb; margin-top:40px;">
                        Kukka EdTech • www.kukka.com.br
                    </p>
                </td>
            </tr>
        </table>
    </body>
    </html>
    ';

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: Kukka EdTech <equipe@kukka.com.br>'
    );

    wp_mail($recipient_email, $subject, $message, $headers);
}



// Adiciona o campo de confirmação de senha no formulário de registro kukka.com.br/minha-conta
function add_confirm_password_field() {
    ?>
    <p class="form-row form-row-wide">
        <label for="reg_confirm_password"><?php _e( 'Confirmar Senha', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
        <input type="password" class="input-text" name="confirm_password" id="reg_confirm_password" autocomplete="off" />
    </p>
    <?php
}
add_action( 'woocommerce_register_form', 'add_confirm_password_field' );

// Valida se a senha e a confirmação de senha coincidem
function validate_password_confirmation( $username, $email, $validation_errors ) {
    if ( isset( $_POST['password'] ) && isset( $_POST['confirm_password'] ) && $_POST['password'] !== $_POST['confirm_password'] ) {
        $validation_errors->add( 'password_error', __( 'As senhas não coincidem.', 'woocommerce' ) );
    }
}
add_action( 'woocommerce_register_post', 'validate_password_confirmation', 10, 3 );

//-----------------------------------------------------------------------------------------
//LÓGICA PARA PÓS COMPRA
// Adiciona o endpoint 'presenteados' à estrutura de URLs
function adicionar_endpoint_presenteados() {
    add_rewrite_endpoint('presenteados', EP_ROOT | EP_PAGES);
}
add_action('init', 'adicionar_endpoint_presenteados');

// Adiciona a aba 'Presenteados' ao menu 'Minha Conta'
add_filter('woocommerce_account_menu_items', 'adicionar_aba_presenteados');
function adicionar_aba_presenteados($items) {
    $logout = $items['customer-logout'];
    unset($items['customer-logout']);

    $user_id = get_current_user_id();
    $pedidos = wc_get_orders([
        'customer_id' => $user_id,
        'post_status' => ['wc-completed', 'wc-processing']
    ]);

    $total_jornadas = 0;
    $emails_utilizados = [];

    foreach ($pedidos as $pedido) {
        foreach ($pedido->get_items() as $item) {
            if (in_array($item->get_product_id(), array(4916, 4918))) {
                $total_jornadas += $item->get_quantity();
            }
        }

        $emails = get_post_meta($pedido->get_id(), 'gift_recipient_email_list');
        if (!empty($emails)) {
            foreach ($emails as $email) {
                $emails_utilizados[] = $email;
            }
        }

        $self_count = intval(get_post_meta($pedido->get_id(), 'gift_self_count', true));
        for ($i = 0; $i < $self_count; $i++) {
            $emails_utilizados[] = 'self-' . $pedido->get_id() . '-' . $i;
        }
    }

    // Considera usuários inativos como utilizados
    $inativos = get_users([
        'role' => 'assinante_inativo',
        'meta_key' => 'from_order_id',
        'meta_value' => $user_id,
        'fields' => 'ID'
    ]);
    foreach ($inativos as $inativo_id) {
        $user_data = get_userdata($inativo_id);
        $emails_utilizados[] = $user_data->user_email;
    }

    $restantes = $total_jornadas - count(array_unique($emails_utilizados));
    $badge = $restantes > 0 ? sprintf(' (%d)', $restantes) : '';

    $items['presenteados'] = 'Presenteados' . $badge;
    $items['customer-logout'] = $logout;

    return $items;
}


add_filter('woocommerce_my_account_menu_items', 'permitir_html_em_aba_presenteados', 20, 1);
function permitir_html_em_aba_presenteados($items) {
    foreach ($items as $key => $item) {
        $items[$key] = wp_kses_post($item);
    }
    return $items;
}


add_filter('woocommerce_account_menu_items', 'adicionar_aba_presenteados');

function exibir_conteudo_presenteados() {
    $user_id = get_current_user_id();
    $pedidos = wc_get_orders(array(
        'customer_id' => $user_id,
        'post_status' => array('wc-completed', 'wc-processing'),
    ));

    $presentes_disponiveis = 0;
    $presentes_utilizados = 0;
    $presentes_por_pedido = [];

    foreach ($pedidos as $pedido) {
        $quantidade = 0;
        foreach ($pedido->get_items() as $item) {
            $product_id = $item->get_product_id();
            if (in_array($product_id, array(4916, 4918))) {
                $quantidade += $item->get_quantity();
            }
        }
        $emails = get_post_meta($pedido->get_id(), 'gift_recipient_email_list');
		$utilizados = is_array($emails) ? count($emails) : 0;

		$self_count = intval(get_post_meta($pedido->get_id(), 'gift_self_count', true));
		// Buscar inativos associados a este comprador
		$inativos = get_users([
			'role' => 'assinante_inativo',
			'meta_key' => 'from_order_id',
			'meta_value' => $user_id,
			'fields' => 'ID'
		]);
		$inativos_count = count($inativos);

		$presentes_utilizados += ($utilizados + $self_count + $inativos_count);


        $presentes_disponiveis += $quantidade;
        $presentes_por_pedido[$pedido->get_id()] = [
            'emails' => $emails,
            'data' => $pedido->get_date_created()->date('d/m/Y')
        ];
    }

    $restantes = $presentes_disponiveis - $presentes_utilizados;

    echo '<h3>' . __('Presentear Jornadas', 'seu-textdomain') . '</h3>';
    echo '<p><strong>Jornadas disponíveis para presentear:</strong> ' . max(0, $restantes) . '</p>';

    if ($restantes > 0) {
        echo '<button id="abrir-modal-presenteado" class="button">Adicionar presenteado</button>';
    }

    echo '<div id="modal-presenteado" style="display:none; background: white; padding: 20px; border: 2px solid #ccc; max-width: 600px;">';
    echo '<h4>Adicionar novo presenteado</h4>';
    echo '<form id="form-presenteado">';
    echo '<p><label>Nome: <input type="text" name="gift_name" required></label></p>';
    echo '<p><label>Email: <input type="email" name="gift_email" required></label></p>';
    echo '<p><label>Mensagem personalizada: <textarea name="gift_message"></textarea></label></p>';
    echo '<input type="hidden" name="action" value="cadastrar_presenteado">';
    echo '<button type="submit" class="button">Salvar presenteado</button>';
    echo ' <button type="button" id="cancel-gift" class="button">Cancelar</button>';
    echo '</form>';
    echo '</div>';

    if ($presentes_utilizados > 0) {
    echo '<table class="shop_table"><thead><tr><th>Pedido</th><th>Presenteado(s)</th><th>Data</th><th>Ações</th></tr></thead><tbody>';

    foreach ($presentes_por_pedido as $pedido_id => $dados) {
        if (empty($dados['emails'])) continue;

        $emails_html = '<ul>';
        foreach ($dados['emails'] as $email) {
            $emails_html .= '<li>' . esc_html($email) . '</li>';
        }
        $emails_html .= '</ul>';

        $acoes_html = '';
        foreach ($dados['emails'] as $email) {
            $acoes_html .= '<button class="remove-gifted-user button" data-order-id="' . esc_attr($pedido_id) . '" data-email="' . esc_attr($email) . '">Remover</button><br>';
        }

        echo '<tr>';
        echo '<td>#' . $pedido_id . '</td>';
        echo '<td>' . $emails_html . '</td>';
        echo '<td>' . $dados['data'] . '</td>';
        echo '<td class="gifted-actions">' . $acoes_html . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}
	// Exibir presenteados inativos
	$usuarios_inativos = get_users([
		'role'    => 'assinante_inativo',
		'meta_key' => 'from_order_id',
		'meta_value' => $user_id // usamos o ID do comprador para filtrar
	]);

	if (!empty($usuarios_inativos)) {
		echo '<h4 style="margin-top:40px;">Presentes Inativos</h4>';
		echo '<table class="shop_table"><thead><tr><th>Email</th><th>Ações</th></tr></thead><tbody>';
		foreach ($usuarios_inativos as $inativo) {
			echo '<tr>';
			echo '<td>' . esc_html($inativo->user_email) . '</td>';
			echo '<td><button class="reactivate-gifted-user" data-user-id="' . esc_attr($inativo->ID) . '">Ativar</button></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
}

}

add_action('woocommerce_account_presenteados_endpoint', 'exibir_conteudo_presenteados');

add_action('wp_ajax_cadastrar_presenteado', 'cadastrar_presenteado');
function cadastrar_presenteado() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Usuário não autenticado');
        return;
    }

    $user_id = get_current_user_id();
    $email = sanitize_email($_POST['gift_email']);
    $nome = sanitize_text_field($_POST['gift_name']);
    $mensagem = sanitize_textarea_field($_POST['gift_message']);

    if (!is_email($email)) {
        wp_send_json_error('Email inválido.');
    }

    $args = array(
        'customer_id' => $user_id,
        'post_status' => array('wc-completed', 'wc-processing')
    );
    $pedidos = wc_get_orders($args);

    $total_jornadas = 0;
    $emails_usados = [];

    foreach ($pedidos as $pedido) {
        foreach ($pedido->get_items() as $item) {
            if (in_array($item->get_product_id(), array(4916, 4918))) {
                $total_jornadas += $item->get_quantity();
            }
        }

        $emails = get_post_meta($pedido->get_id(), 'gift_recipient_email_list');
        if (!empty($emails)) {
            foreach ($emails as $e) {
                $emails_usados[] = $e;
            }
        }
    }

    $emails_unicos = array_unique($emails_usados);
    $quantidade_usada = count($emails_unicos);

    // Verifica se ainda há jornadas disponíveis
    if ($quantidade_usada >= $total_jornadas) {
        wp_send_json_error('Limite de presenteados atingido.');
    }

    // Evita presente duplicado
    if (in_array($email, $emails_unicos)) {
        wp_send_json_error('Este e-mail já foi presenteado anteriormente.');
    }

    // Verifica se está tentando se auto-presentear após já ter usado uma unidade para si
    $current_user = wp_get_current_user();
    if ($email === $current_user->user_email) {
        $ja_usou_para_si = 0;
        foreach ($pedidos as $pedido) {
            $ja_usou_para_si += intval(get_post_meta($pedido->get_id(), 'gift_self_count', true));
        }
        if ($ja_usou_para_si > 0) {
            wp_send_json_error('Você já usou uma jornada para você mesmo.');
        }
    }

    foreach ($pedidos as $pedido) {
        $pedido_id = $pedido->get_id();
        $emails_existentes = get_post_meta($pedido_id, 'gift_recipient_email_list');
        $quantidade = 0;
        foreach ($pedido->get_items() as $item) {
            if (in_array($item->get_product_id(), array(4916, 4918))) {
                $quantidade = $item->get_quantity();
                break;
            }
        }

        if (count($emails_existentes) < $quantidade) {
            add_post_meta($pedido_id, 'gift_recipient_email_list', $email);

            if (!email_exists($email)) {
                $senha = wp_generate_password();
                $new_user_id = wp_create_user($email, $senha, $email);

                if (!is_wp_error($new_user_id)) {
                    wp_update_user([
                        'ID' => $new_user_id,
                        'first_name' => $nome
                    ]);
                    set_user_role_to_subscriber($new_user_id);
                    update_user_meta($new_user_id, 'mensagem_presente', $mensagem);
                    send_gift_email($email, $senha, $mensagem);
                }
            } else {
                $user = get_user_by('email', $email);
                if ($user && !in_array('subscriber', $user->roles)) {
                    $user->add_role('subscriber');
                }
            }

            wp_send_json_success('Presenteado cadastrado com sucesso.');
        }
    }

    wp_send_json_error('Não foi possível cadastrar o presenteado.');
}


add_action('wp_ajax_remover_presenteado', 'remover_presenteado');
function remover_presenteado() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Acesso negado.');
    }

    $order_id = intval($_POST['order_id']);
    $email = sanitize_email($_POST['email']);
    $emails = get_post_meta($order_id, 'gift_recipient_email_list');

    if (!empty($emails)) {
        foreach ($emails as $key => $e) {
            if ($e === $email) {
                // Remove o e-mail do pedido
                delete_post_meta($order_id, 'gift_recipient_email_list', $e);

                $user = get_user_by('email', $email);
                if ($user) {
                    $user_obj = new WP_User($user->ID);

                    // Remove 'subscriber' (assinante)
                    if (in_array('subscriber', $user_obj->roles)) {
                        $user_obj->remove_role('subscriber');
                    }

                    // Adiciona 'assinante_inativo' se ainda não tiver
                    if (!in_array('assinante_inativo', $user_obj->roles)) {
                        $user_obj->add_role('assinante_inativo');
                    }

                    // Só mantém 'customer' se o usuário já tinha essa função
                    // (Ou seja, não adiciona se for apenas presenteado)
                    // Nada a fazer aqui

                    // Armazena quem inativou
                    update_user_meta($user->ID, 'from_order_id', get_current_user_id());
                }

                wp_send_json_success('Presenteado marcado como inativo.');
            }
        }
    }

    wp_send_json_error('Presenteado não encontrado.');
}





add_action('wp_footer', 'gifted_users_removal_script');
function gifted_users_removal_script() {
    if (is_account_page()) {
        ?>
        <style>
            #custom-modal {
                display: none;
                position: fixed;
                z-index: 9999;
                left: 0; top: 0; width: 100%; height: 100%;
                background: rgba(0, 0, 0, 0.6);
            }
            #custom-modal .modal-content {
                background: #fff;
                margin: 10% auto;
                padding: 20px;
                border-radius: 8px;
                width: 90%;
                max-width: 400px;
                text-align: center;
                position: relative;
            }
            #custom-modal .modal-buttons {
                margin-top: 20px;
                display: flex;
                justify-content: center;
                gap: 15px;
            }
            #custom-modal button {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
            }
            #modal-confirm { background-color: #5d1973; color: white; }
            #modal-cancel { background-color: #ccc; }
        </style>

        <div id="custom-modal">
            <div class="modal-content">
                <p id="modal-message">Tem certeza que deseja remover este presenteado?</p>
                <div class="modal-buttons">
                    <button id="modal-confirm">Confirmar</button>
                    <button id="modal-cancel">Cancelar</button>
                </div>
            </div>
        </div>

        <script>
			jQuery(function($) {
				let removeData = {};

				$('#abrir-modal-presenteado').click(function() {
					$('#modal-presenteado').fadeIn();
				});

				$('#cancel-gift').click(function() {
					$('#modal-presenteado').fadeOut();
				});

				$('#form-presenteado').submit(function(e) {
					e.preventDefault();
					var data = $(this).serialize();
					$('#form-presenteado button').prop('disabled', true); // desativa envio duplo
					$.post('<?php echo admin_url("admin-ajax.php"); ?>', data, function(response) {
						if (response.success) {
							mostrarModal('🎁 Presenteado cadastrado com sucesso!', true);
						} else {
							mostrarModal('❌ Erro: ' + response.data);
							$('#form-presenteado button').prop('disabled', false); // reativa
						}
					});
				});

				$('.remove-gifted-user').click(function() {
					removeData.order_id = $(this).data('order-id');
					removeData.email = $(this).data('email');
					mostrarModal('⚠️ Ao remover, o presente será desativado e não poderá ser reaproveitado em outro cadastro. Deseja continuar?', false, true);
				});

				$('#modal-confirm').click(function() {
					if (removeData.email) {
						$.post('<?php echo admin_url("admin-ajax.php"); ?>', {
							action: 'remover_presenteado',
							order_id: removeData.order_id,
							email: removeData.email
						}, function(response) {
							if (response.success) {
								mostrarModal('✅ Presenteado removido com sucesso!', true);
							} else {
								mostrarModal('❌ Erro: ' + response.data);
							}
							removeData = {};
						});
					} else {
						fecharModal();
					}
				});

				$('#modal-cancel').click(function() {
					fecharModal();
				});

				$('.reactivate-gifted-user').click(function() {
					removeData.user_id = $(this).data('user-id');
					removeData.email = null;

					mostrarModal('⚠️ Deseja reativar o presenteado? Ele terá acesso à jornada novamente.', false, true);
				});

				$('#modal-confirm').off('click').on('click', function() {
					$('#modal-confirm').prop('disabled', true); // 🔒 Evita múltiplos cliques

					if (removeData.user_id && !removeData.email) {
						$.post('<?php echo admin_url("admin-ajax.php"); ?>', {
							action: 'reativar_presenteado',
							user_id: removeData.user_id
						}, function(response) {
							if (response.success) {
								mostrarModal('✅ Presenteado reativado com sucesso!', true);
							} else {
								mostrarModal('❌ Erro: ' + response.data);
								$('#modal-confirm').prop('disabled', false); // 🔓 Reativa se erro
							}
							removeData = {};
						});
					} else if (removeData.email) {
						// Caso de remoção
						$.post('<?php echo admin_url("admin-ajax.php"); ?>', {
							action: 'remover_presenteado',
							order_id: removeData.order_id,
							email: removeData.email
						}, function(response) {
							if (response.success) {
								mostrarModal('✅ Presenteado removido com sucesso!', true);
							} else {
								mostrarModal('❌ Erro: ' + response.data);
							}
							removeData = {};
						});
					}
				});


				function mostrarModal(mensagem, reload = false, confirmacao = false) {
					$('#modal-message').text(mensagem);
					if (confirmacao) {
						$('#modal-confirm').show();
						$('#modal-cancel').text('Cancelar');
					} else {
						$('#modal-confirm').hide();
						$('#modal-cancel').text('Fechar');
					}
					$('#custom-modal').fadeIn();

					if (!confirmacao && reload) {
						setTimeout(function() {
							location.reload();
						}, 2000);
					}
				}

				function fecharModal() {
					$('#custom-modal').fadeOut();
					removeData = {};
				}
			});
			</script>

        <?php
    }
}

add_action('wp_ajax_reativar_presenteado', 'reativar_presenteado');
function reativar_presenteado() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Acesso negado.');
    }

    $user_id = intval($_POST['user_id']);
    $user = get_user_by('id', $user_id);

    if (!$user || !in_array('assinante_inativo', $user->roles)) {
        wp_send_json_error('Usuário não encontrado ou não é inativo.');
    }

    $wp_user = new WP_User($user_id);

    // Remove a função 'assinante_inativo'
    $wp_user->remove_role('assinante_inativo');

    // Reativa apenas com a função 'subscriber', sem adicionar 'customer' automaticamente
    if (!in_array('subscriber', $wp_user->roles)) {
        $wp_user->add_role('subscriber');
    }

    // OBS: Não adiciona a função 'customer' aqui, pois é um presenteado, não comprador

    // Reassocia o presente ao pedido (se ainda não estiver registrado)
    $email_normalizado = strtolower($user->user_email);
    $order_owner_id = get_user_meta($user_id, 'from_order_id', true);
    if ($order_owner_id) {
        $orders = wc_get_orders([
            'customer_id' => $order_owner_id,
            'post_status' => ['wc-completed', 'wc-processing']
        ]);

        foreach ($orders as $order) {
            $order_id = $order->get_id();
            $emails = get_post_meta($order_id, 'gift_recipient_email_list');
            $emails_normalizados = array_map('strtolower', $emails);

            if (!in_array($email_normalizado, $emails_normalizados)) {
                add_post_meta($order_id, 'gift_recipient_email_list', $user->user_email);
                break;
            }
        }
    }

    delete_user_meta($user_id, 'from_order_id');

    wp_send_json_success('Presenteado reativado com sucesso.');
}





add_action('woocommerce_after_checkout_form', 'gift_email_dynamic_script');
function gift_email_dynamic_script() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        function togglePerUnitEmailFields() {
            $('.gift-choice-block').each(function() {
                const $block = $(this);
                const $radioGift = $block.find('input[value="present"]');
                const $radioSelf = $block.find('input[value="self"]');
                const $emailField = $block.find('.gift-email-field');

                if ($radioGift.is(':checked')) {
                    $emailField.slideDown();
                } else {
                    $emailField.slideUp().find('input').val('');
                }
            });
        }

        $(document).on('change', 'input[name^="gift_option_"]', function() {
            togglePerUnitEmailFields();
        });

        togglePerUnitEmailFields(); // roda ao carregar
    });
    </script>
    <?php
}



