<?php
/**
 * Page d'administration : brief, génération, relecture et création du brouillon.
 */
class ASSREDAC_Admin_Page {

	private const LANGUAGES = array( 'français' );

	/**
	 * @var ASSREDAC_Article_Generator
	 */
	private $generator;

	/**
	 * L'écran à afficher : 'form' ou 'review'.
	 *
	 * @var string
	 */
	private $screen = 'form';

	/**
	 * Le brief en cours, vide au premier affichage.
	 *
	 * @var array
	 */
	private $brief = array(
		'sujet'    => '',
		'mot_cle'  => '',
		'ton'      => '',
		'langue'   => 'français',
		'consigne' => '',
	);

	/**
	 * @var array L'article généré (titre, contenu).
	 */
	private $article = array();

	/**
	 * @var array Les messages d'erreur à afficher.
	 */
	private $errors = array();

	public function __construct( $generator ) {
		$this->generator = $generator;
	}

	/**
	 * Lit les champs du brief envoyés par le formulaire, et les nettoie.
	 *
	 * @return array Le brief, avec toujours les 5 clés (vides si le champ est absent).
	 */
	private function read_brief() {
		$brief = array();

		// Les champs qui tiennent sur une seule ligne.
		foreach ( array( 'sujet', 'mot_cle', 'ton', 'langue' ) as $champ ) {
			$brief[ $champ ] = isset( $_POST[ $champ ] ) ? sanitize_text_field( wp_unslash( $_POST[ $champ ] ) ) : '';
		}

		// La consigne peut tenir sur plusieurs lignes : on garde les retours à la ligne.
		$brief['consigne'] = isset( $_POST['consigne'] ) ? sanitize_textarea_field( wp_unslash( $_POST['consigne'] ) ) : '';

		return $brief;
	}

    /**
	 * Vérifie que le brief respecte les règles du formulaire.
	 *
	 * @param array $brief Le brief lu par read_brief().
	 * @return array Les messages d'erreur (un tableau vide si le brief est valide).
	 */
	private function validate_brief( $brief ) {
		$errors = array();

		// Les champs obligatoires, avec le message à afficher s'ils sont vides.
		$required = array(
			'sujet'   => 'Le sujet est obligatoire.',
			'mot_cle' => 'Le mot-clé est obligatoire.',
			'ton'     => 'Le ton est obligatoire.',
		);

		foreach ( $required as $champ => $message ) {
			if ( '' === $brief[ $champ ] ) {
				$errors[] = $message;
			}
		}

		// La langue doit faire partie de la liste proposée dans le formulaire.
		if ( ! in_array( $brief['langue'], self::LANGUAGES, true ) ) {
			$errors[] = "La langue choisie n'est pas disponible.";
		}

		return $errors;
	}
		
    /**
	 * Lit le titre et le contenu renvoyés par les champs cachés de la relecture, et les renettoie.
	 *
	 * @return array L'article nettoyé (titre, contenu).
	 */
	private function read_article() {
		$article = array(
			'titre'   => isset( $_POST['titre'] ) ? wp_unslash( $_POST['titre'] ) : '',
			'contenu' => isset( $_POST['contenu'] ) ? wp_unslash( $_POST['contenu'] ) : '',
		);

		// Ces données reviennent du navigateur : on applique les mêmes règles que pour la réponse du modèle.
		return $this->generator->clean_article( $article );
	}

    /**
	 * Crée le brouillon à partir d'un titre et d'un contenu déjà nettoyés.
	 *
	 * @param string $titre   Le titre de l'article.
	 * @param string $contenu Le contenu HTML de l'article.
	 * @return int|WP_Error L'identifiant du brouillon créé, ou une erreur.
	 */
	private function create_draft( $titre, $contenu ) {
		$post = array(
			'post_title'   => $titre,
			'post_content' => $contenu,
			'post_status'  => 'draft',
			'post_author'  => get_current_user_id(),
		);

		return wp_insert_post( wp_slash( $post ), true );
	}

    

	/**
	 * Affiche le formulaire du brief, pré-rempli avec le brief en cours.
	 */
	private function render_form() {
		?>
		<form method="post" id="assredac-form">
			<?php wp_nonce_field( 'assredac_generate', 'assredac_nonce' ); ?>

			<p>
				<label for="assredac-sujet">Sujet</label><br>
				<input type="text" id="assredac-sujet" name="sujet" class="regular-text" required
					value="<?php echo esc_attr( $this->brief['sujet'] ); ?>">
			</p>

			<p>
				<label for="assredac-mot-cle">Mot-clé</label><br>
				<input type="text" id="assredac-mot-cle" name="mot_cle" class="regular-text" required
					value="<?php echo esc_attr( $this->brief['mot_cle'] ); ?>">
			</p>

			<p>
				<label for="assredac-ton">Ton</label><br>
				<input type="text" id="assredac-ton" name="ton" class="regular-text" required
					value="<?php echo esc_attr( $this->brief['ton'] ); ?>">
			</p>

			<p>
				<label for="assredac-langue">Langue</label><br>
				<select id="assredac-langue" name="langue">
					<?php foreach ( self::LANGUAGES as $langue ) : ?>
						<option value="<?php echo esc_attr( $langue ); ?>" <?php selected( $this->brief['langue'], $langue ); ?>>
							<?php echo esc_html( $langue ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<label for="assredac-consigne">Consigne (facultative)</label><br>
				<textarea id="assredac-consigne" name="consigne" class="large-text" rows="4" maxlength="500"
					aria-describedby="assredac-consigne-aide"><?php echo esc_textarea( $this->brief['consigne'] ); ?></textarea>
				<span id="assredac-consigne-aide" class="description">500 caractères maximum. La longueur de l'article est fixée à environ 300 mots.</span>
			</p>

			<p>
				<button type="submit" name="assredac_action" value="generate" class="button button-primary">Générer l'article</button>
			</p>
		</form>
		<?php
	}

	/**
	 * Affiche la proposition générée, avec les champs cachés et les trois boutons.
	 */
	private function render_review() {
		?>
		<h2><?php echo esc_html( $this->article['titre'] ); ?></h2>
		<div class="assredac-apercu">
			<?php echo wp_kses_post( $this->article['contenu'] ); ?>
		</div>

		<form method="post" id="assredac-review">
			<?php wp_nonce_field( 'assredac_generate', 'assredac_nonce' ); ?>

			<?php foreach ( $this->brief as $champ => $valeur ) : ?>
				<input type="hidden" name="<?php echo esc_attr( $champ ); ?>" value="<?php echo esc_attr( $valeur ); ?>">
			<?php endforeach; ?>
			<input type="hidden" name="titre" value="<?php echo esc_attr( $this->article['titre'] ); ?>">
			<input type="hidden" name="contenu" value="<?php echo esc_attr( $this->article['contenu'] ); ?>">

			<p>
				<button type="submit" name="assredac_action" value="create_draft" class="button button-primary">Modifier dans l'éditeur</button>
				<button type="submit" name="assredac_action" value="regenerate" class="button">Régénérer</button>
				<button type="submit" name="assredac_action" value="edit_brief" class="button">Modifier le brief</button>
			</p>
			<p class="description">« Modifier dans l'éditeur » crée le brouillon et l'ouvre dans l'éditeur de WordPress. Rien n'est publié.</p>
		</form>
		<?php
	}
		
    /**
	 * Affiche la page : le cadre, le titre, les erreurs, puis l'écran demandé.
	 */
	public function render_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php foreach ( $this->errors as $error ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div>
			<?php endforeach; ?>

			<?php
			if ( 'review' === $this->screen ) {
				$this->render_review();
			} else {
				$this->render_form();
			}
			?>
		</div>
		<?php
	}

    /**
	 * Traite le formulaire envoyé, avant tout affichage (hook load-…).
	 */
	public function handle_request() {
		// Premier affichage : aucun formulaire envoyé, rien à traiter.
		if ( ! isset( $_POST['assredac_action'] ) ) {
			return;
		}

        // Check les droits, puis le nonce
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		check_admin_referer( 'assredac_generate', 'assredac_nonce' );

		$action      = sanitize_key( wp_unslash( $_POST['assredac_action'] ) );
		$this->brief = $this->read_brief();

		if ( 'generate' === $action || 'regenerate' === $action ) {
			$this->handle_generate();
		} elseif ( 'create_draft' === $action ) {
			$this->handle_create_draft();
		}
	}

	/**
	 * Valide le brief, puis lance la génération.
	 */
	private function handle_generate() {
		$this->errors = $this->validate_brief( $this->brief );
		if ( ! empty( $this->errors ) ) {
			return; // Retour au formulaire, pré-rempli, avec les erreurs.
		}

		$article = $this->generator->generate( $this->brief );
		if ( is_wp_error( $article ) ) {
			$this->errors[] = $article->get_error_message();
			return;
		}

		$this->article = $article;
		$this->screen  = 'review';
	}

	/**
	 * Crée le brouillon à partir de l'article relu, puis ouvre l'éditeur.
	 */
	private function handle_create_draft() {
		$article = $this->read_article();
		$post_id = $this->create_draft( $article['titre'], $article['contenu'] );

		if ( is_wp_error( $post_id ) ) {
			// On reste sur la relecture, avec l'erreur et l'article.
			$this->errors[] = $post_id->get_error_message();
			$this->article  = $article;
			$this->screen   = 'review';
			return;
		}

		wp_safe_redirect( get_edit_post_link( $post_id, 'url' ) );
		exit;
	}
		
    /**
	 * Ajoute la page au menu « Articles », et branche son traitement avant affichage.
	 * Appelée par WordPress au hook admin_menu.
	 */
	public function register_menu() {
		$hook_suffix = add_posts_page(
			'Assistant de rédaction',
			'Assistant de rédaction',
			'edit_posts',
			'assredac',
			array( $this, 'render_page' )
		);

		if ( false === $hook_suffix ) {
			return;
		}

		add_action( 'load-' . $hook_suffix, array( $this, 'handle_request' ) );
	}
}