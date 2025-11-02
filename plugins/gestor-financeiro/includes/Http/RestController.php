<?php

/**
 * REST API controller.
 *
 * @package GestorFinanceiro
 * @subpackage Http
 */

declare(strict_types=1);

namespace GestorFinanceiro\Http;

use GestorFinanceiro\DB\Repositories\EstabelecimentosRepository;
use GestorFinanceiro\DB\Repositories\FornecedoresRepository;
use GestorFinanceiro\DB\Repositories\FuncionariosRepository;
use GestorFinanceiro\DB\Repositories\DespesasRepository;
use GestorFinanceiro\DB\Repositories\ReceitasRepository;
use GestorFinanceiro\DB\Repositories\ObrigacoesRepository;
use GestorFinanceiro\DB\Repositories\SettingsRepository;
use GestorFinanceiro\Security\Permissions;
use GestorFinanceiro\Security\Nonces;
use GestorFinanceiro\Features\Reports;
use GestorFinanceiro\Features\CSV;
use GestorFinanceiro\Features\Apartment;

/**
 * REST API endpoints controller.
 */
class RestController
{

    /**
     * Namespace for REST API.
     */
    private const NAMESPACE = 'gestor-financeiro/v1';

    /**
     * Initialize REST routes.
     *
     * @return void
     */
    public function init(): void
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register all REST routes.
     *
     * @return void
     */
    public function register_routes(): void
    {
        $this->register_estabelecimentos_routes();
        $this->register_fornecedores_routes();
        $this->register_funcionarios_routes();
        $this->register_despesas_routes();
        $this->register_receitas_routes();
        $this->register_obrigacoes_routes();
        $this->register_settings_routes();
        $this->register_dashboard_routes();
        $this->register_calendar_routes();
        $this->register_salaries_routes();
        $this->register_reports_routes();
		$this->register_csv_routes();
		$this->register_apartment_routes();
		$this->register_help_routes();
		$this->register_admin_routes();
	}

    /**
     * Register estabelecimentos routes.
     *
     * @return void
     */
    private function register_estabelecimentos_routes(): void
    {
        $base = 'estabelecimentos';

        register_rest_route(
            self::NAMESPACE,
            '/' . $base,
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_estabelecimentos'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'POST',
                    'callback'            => array($this, 'create_estabelecimento'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/' . $base . '/(?P<id>\d+)',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_estabelecimento'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'PUT',
                    'callback'            => array($this, 'update_estabelecimento'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
                array(
                    'methods'             => 'DELETE',
                    'callback'            => array($this, 'delete_estabelecimento'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );
    }

    /**
     * Register fornecedores routes.
     *
     * @return void
     */
    private function register_fornecedores_routes(): void
    {
        $base = 'fornecedores';

        register_rest_route(
            self::NAMESPACE,
            '/' . $base,
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_fornecedores'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'POST',
                    'callback'            => array($this, 'create_fornecedor'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/' . $base . '/(?P<id>\d+)',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_fornecedor'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'PUT',
                    'callback'            => array($this, 'update_fornecedor'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
                array(
                    'methods'             => 'DELETE',
                    'callback'            => array($this, 'delete_fornecedor'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );
    }

    /**
     * Register funcionarios routes.
     *
     * @return void
     */
    private function register_funcionarios_routes(): void
    {
        $base = 'funcionarios';

        register_rest_route(
            self::NAMESPACE,
            '/' . $base,
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_funcionarios'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'POST',
                    'callback'            => array($this, 'create_funcionario'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/' . $base . '/(?P<id>\d+)',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_funcionario'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'PUT',
                    'callback'            => array($this, 'update_funcionario'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
                array(
                    'methods'             => 'DELETE',
                    'callback'            => array($this, 'delete_funcionario'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );
    }

    /**
     * Register despesas routes.
     *
     * @return void
     */
    private function register_despesas_routes(): void
    {
        $base = 'despesas';

        register_rest_route(
            self::NAMESPACE,
            '/' . $base,
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_despesas'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'POST',
                    'callback'            => array($this, 'create_despesa'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/' . $base . '/(?P<id>\d+)',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_despesa'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'PUT',
                    'callback'            => array($this, 'update_despesa'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
                array(
                    'methods'             => 'DELETE',
                    'callback'            => array($this, 'delete_despesa'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
                array(
                    'methods'             => 'PATCH',
                    'callback'            => array($this, 'mark_despesa_paid'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );
    }

    /**
     * Register receitas routes.
     *
     * @return void
     */
    private function register_receitas_routes(): void
    {
        $base = 'receitas';

        register_rest_route(
            self::NAMESPACE,
            '/' . $base,
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_receitas'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'POST',
                    'callback'            => array($this, 'create_receita'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/' . $base . '/(?P<id>\d+)',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_receita'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'PUT',
                    'callback'            => array($this, 'update_receita'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
                array(
                    'methods'             => 'DELETE',
                    'callback'            => array($this, 'delete_receita'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );
    }

    /**
     * Register obrigacoes routes.
     *
     * @return void
     */
    private function register_obrigacoes_routes(): void
    {
        $base = 'obrigacoes';

        register_rest_route(
            self::NAMESPACE,
            '/' . $base,
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_obrigacoes'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'POST',
                    'callback'            => array($this, 'create_obrigacao'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/' . $base . '/(?P<id>\d+)',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_obrigacao'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'PUT',
                    'callback'            => array($this, 'update_obrigacao'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
                array(
                    'methods'             => 'DELETE',
                    'callback'            => array($this, 'delete_obrigacao'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );
    }

    /**
     * Register settings routes.
     *
     * @return void
     */
    private function register_settings_routes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/settings',
            array(
                array(
                    'methods'             => 'GET',
                    'callback'            => array($this, 'get_settings'),
                    'permission_callback' => array($this, 'check_view_permission'),
                ),
                array(
                    'methods'             => 'PUT',
                    'callback'            => array($this, 'update_settings'),
                    'permission_callback' => array($this, 'check_edit_permission'),
                ),
            )
        );
    }

    /**
     * Register dashboard routes.
     *
     * @return void
     */
    private function register_dashboard_routes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/dashboard/summary',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_dashboard_summary'),
                'permission_callback' => array($this, 'check_view_permission'),
            )
        );
    }

    /**
     * Register calendar routes.
     *
     * @return void
     */
    private function register_calendar_routes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/calendar',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_calendar_events'),
                'permission_callback' => array($this, 'check_view_permission'),
            )
        );
    }

    /**
     * Register salaries routes.
     *
     * @return void
     */
    private function register_salaries_routes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/salaries',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_salaries'),
                'permission_callback' => array($this, 'check_view_permission'),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/salaries/(?P<id>\d+)/mark-paid',
            array(
                'methods'             => 'PATCH',
                'callback'            => array($this, 'mark_salary_paid'),
                'permission_callback' => array($this, 'check_edit_permission'),
            )
        );
    }

    /**
     * Register reports routes.
     *
     * @return void
     */
    private function register_reports_routes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/reports/monthly',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_monthly_report'),
                'permission_callback' => array($this, 'check_view_permission'),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/reports/monthly/export',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'export_monthly_report'),
                'permission_callback' => array($this, 'check_view_permission'),
            )
        );
    }

    /**
     * Register CSV routes.
     *
     * @return void
     */
    private function register_csv_routes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/csv/export',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'export_csv'),
                'permission_callback' => array($this, 'check_view_permission'),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/csv/import',
            array(
                'methods'             => 'POST',
                'callback'            => array($this, 'import_csv'),
                'permission_callback' => array($this, 'check_edit_permission'),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/csv/import/execute',
            array(
                'methods'             => 'POST',
                'callback'            => array($this, 'execute_csv_import'),
                'permission_callback' => array($this, 'check_edit_permission'),
            )
        );
    }

    /**
     * Register apartment routes.
     *
     * @return void
     */
    private function register_apartment_routes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/apartments/categories',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_apartment_categories'),
                'permission_callback' => array($this, 'check_view_permission'),
            )
        );

        register_rest_route(
            self::NAMESPACE,
            '/apartments/(?P<id>\d+)/generate-recurring',
            array(
                'methods'             => 'POST',
                'callback'            => array($this, 'generate_apartment_recurring'),
                'permission_callback' => array($this, 'check_edit_permission'),
            )
        );
    }

    /**
     * Register help routes.
     *
     * @return void
     */
    private function register_help_routes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/help/guide',
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'get_help_guide'),
                'permission_callback' => array($this, 'check_view_permission'),
            )
        );
    }

    /**
     * Check view permission.
     *
     * @param \WP_REST_Request $request Request object.
     * @return bool
     */
    public function check_view_permission(\WP_REST_Request $request): bool
    {
        return Permissions::check_rest_permission('gestor_ver');
    }

    /**
     * Check edit permission.
     *
     * @param \WP_REST_Request $request Request object.
     * @return bool|\WP_Error
     */
    public function check_edit_permission(\WP_REST_Request $request)
    {
        if (! Permissions::check_rest_permission('gestor_editar')) {
            return new \WP_Error(
                'forbidden',
                __('Não tem permissão para realizar esta ação.', 'gestor-financeiro'),
                array('status' => 403)
            );
        }
        return true;
    }

    /**
     * Send error response.
     *
     * @param string $code Error code.
     * @param string $message Error message.
     * @param int    $status HTTP status code.
     * @param array  $data Additional error data.
     * @return \WP_Error
     */
    private function error_response(string $code, string $message, int $status = 400, array $data = array()): \WP_Error
    {
        return new \WP_Error(
            $code,
            $message,
            array_merge(array('status' => $status), $data)
        );
    }

    /**
     * Send success response.
     *
     * @param mixed $data Response data.
     * @param int   $status HTTP status code.
     * @return \WP_REST_Response
     */
    private function success_response($data, int $status = 200): \WP_REST_Response
    {
        return new \WP_REST_Response($data, $status);
    }

    // Estabelecimentos endpoints.
    public function get_estabelecimentos(\WP_REST_Request $request): \WP_REST_Response
    {
        $repo = new EstabelecimentosRepository();
        $estabelecimentos = $repo->findAll(array(), array('nome' => 'ASC'));
        return $this->success_response($estabelecimentos, 200);
    }

    public function get_estabelecimento(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new EstabelecimentosRepository();
        $estabelecimento = $repo->find($id);

        if (! $estabelecimento) {
            return $this->error_response(
                'not_found',
                __('Estabelecimento não encontrado.', 'gestor-financeiro'),
                404
            );
        }

        return $this->success_response($estabelecimento, 200);
    }

    public function create_estabelecimento(\WP_REST_Request $request)
    {
        $data = $this->sanitize_estabelecimento_data($request->get_json_params());
        $validation = $this->validate_estabelecimento_data($data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $repo = new EstabelecimentosRepository();
        $id = $repo->create($data);

        if (false === $id) {
            return $this->error_response(
                'creation_failed',
                __('Erro ao criar estabelecimento.', 'gestor-financeiro'),
                500
            );
        }

        $estabelecimento = $repo->find($id);
        return $this->success_response($estabelecimento, 201);
    }

    public function update_estabelecimento(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new EstabelecimentosRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Estabelecimento não encontrado.', 'gestor-financeiro'),
                404
            );
        }

        $data = $this->sanitize_estabelecimento_data($request->get_json_params());
        $validation = $this->validate_estabelecimento_data($data, $id);
        if (is_wp_error($validation)) {
            return $validation;
        }

        if (! $repo->update($id, $data)) {
            return $this->error_response(
                'update_failed',
                __('Erro ao atualizar estabelecimento.', 'gestor-financeiro'),
                500
            );
        }

        $estabelecimento = $repo->find($id);
        return $this->success_response($estabelecimento, 200);
    }

    public function delete_estabelecimento(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new EstabelecimentosRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Estabelecimento não encontrado.', 'gestor-financeiro'),
                404
            );
        }

        if (! $repo->delete($id)) {
            return $this->error_response(
                'deletion_failed',
                __('Erro ao eliminar estabelecimento.', 'gestor-financeiro'),
                500
            );
        }

        return $this->success_response(null, 204);
    }

    // Fornecedores endpoints.
    public function get_fornecedores(\WP_REST_Request $request): \WP_REST_Response
    {
        $repo = new FornecedoresRepository();
        $fornecedores = $repo->findAll(array(), array('nome' => 'ASC'));
        return $this->success_response($fornecedores, 200);
    }

    public function get_fornecedor(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new FornecedoresRepository();
        $fornecedor = $repo->find($id);

        if (! $fornecedor) {
            return $this->error_response(
                'not_found',
                __('Fornecedor não encontrado.', 'gestor-financeiro'),
                404
            );
        }

        return $this->success_response($fornecedor, 200);
    }

    public function create_fornecedor(\WP_REST_Request $request)
    {
        $data = $this->sanitize_fornecedor_data($request->get_json_params());
        $validation = $this->validate_fornecedor_data($data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $repo = new FornecedoresRepository();
        $id = $repo->create($data);

        if (false === $id) {
            return $this->error_response(
                'creation_failed',
                __('Erro ao criar fornecedor.', 'gestor-financeiro'),
                500
            );
        }

        $fornecedor = $repo->find($id);
        return $this->success_response($fornecedor, 201);
    }

    public function update_fornecedor(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new FornecedoresRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Fornecedor não encontrado.', 'gestor-financeiro'),
                404
            );
        }

        $data = $this->sanitize_fornecedor_data($request->get_json_params());
        $validation = $this->validate_fornecedor_data($data, $id);
        if (is_wp_error($validation)) {
            return $validation;
        }

        if (! $repo->update($id, $data)) {
            return $this->error_response(
                'update_failed',
                __('Erro ao atualizar fornecedor.', 'gestor-financeiro'),
                500
            );
        }

        $fornecedor = $repo->find($id);
        return $this->success_response($fornecedor, 200);
    }

    public function delete_fornecedor(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new FornecedoresRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Fornecedor não encontrado.', 'gestor-financeiro'),
                404
            );
        }

        if (! $repo->delete($id)) {
            return $this->error_response(
                'deletion_failed',
                __('Erro ao eliminar fornecedor.', 'gestor-financeiro'),
                500
            );
        }

        return $this->success_response(null, 204);
    }

    // Funcionarios endpoints.
    public function get_funcionarios(\WP_REST_Request $request): \WP_REST_Response
    {
        $repo = new FuncionariosRepository();
        $filters = array();
        if ($request->get_param('estabelecimento_id')) {
            $filters['estabelecimento_id'] = (int) $request->get_param('estabelecimento_id');
        }
        $funcionarios = $repo->findAll($filters, array('nome' => 'ASC'));
        return $this->success_response($funcionarios, 200);
    }

    public function get_funcionario(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new FuncionariosRepository();
        $funcionario = $repo->find($id);

        if (! $funcionario) {
            return $this->error_response(
                'not_found',
                __('Funcionário não encontrado.', 'gestor-financeiro'),
                404
            );
        }

        return $this->success_response($funcionario, 200);
    }

    public function create_funcionario(\WP_REST_Request $request)
    {
        $data = $this->sanitize_funcionario_data($request->get_json_params());
        $validation = $this->validate_funcionario_data($data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $repo = new FuncionariosRepository();
        $id = $repo->create($data);

        if (false === $id) {
            return $this->error_response(
                'creation_failed',
                __('Erro ao criar funcionário.', 'gestor-financeiro'),
                500
            );
        }

        $funcionario = $repo->find($id);
        return $this->success_response($funcionario, 201);
    }

    public function update_funcionario(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new FuncionariosRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Funcionário não encontrado.', 'gestor-financeiro'),
                404
            );
        }

        $data = $this->sanitize_funcionario_data($request->get_json_params());
        $validation = $this->validate_funcionario_data($data, $id);
        if (is_wp_error($validation)) {
            return $validation;
        }

        if (! $repo->update($id, $data)) {
            return $this->error_response(
                'update_failed',
                __('Erro ao atualizar funcionário.', 'gestor-financeiro'),
                500
            );
        }

        $funcionario = $repo->find($id);
        return $this->success_response($funcionario, 200);
    }

    public function delete_funcionario(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new FuncionariosRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Funcionário não encontrado.', 'gestor-financeiro'),
                404
            );
        }

        if (! $repo->delete($id)) {
            return $this->error_response(
                'deletion_failed',
                __('Erro ao eliminar funcionário.', 'gestor-financeiro'),
                500
            );
        }

        return $this->success_response(null, 204);
    }

    // Despesas endpoints.
    public function get_despesas(\WP_REST_Request $request): \WP_REST_Response
    {
        $repo = new DespesasRepository();
        $filters = array();

        if ($request->get_param('estabelecimento_id')) {
            $filters['estabelecimento_id'] = (int) $request->get_param('estabelecimento_id');
        }
        if ($request->get_param('start_date')) {
            $filters['data >='] = sanitize_text_field($request->get_param('start_date'));
        }
        if ($request->get_param('end_date')) {
            $filters['data <='] = sanitize_text_field($request->get_param('end_date'));
        }
        if ($request->get_param('pago') !== null) {
            $filters['pago'] = (int) $request->get_param('pago');
        }

        $order_by = array('data' => 'DESC', 'id' => 'DESC');
        $limit = $request->get_param('per_page') ? (int) $request->get_param('per_page') : null;
        $offset = $request->get_param('page') ? ((int) $request->get_param('page') - 1) * ($limit ?: 10) : null;

        $despesas = $repo->findAll($filters, $order_by, $limit, $offset);
        return $this->success_response($despesas, 200);
    }

    public function get_despesa(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new DespesasRepository();
        $despesa = $repo->find($id);

        if (! $despesa) {
            return $this->error_response(
                'not_found',
                __('Despesa não encontrada.', 'gestor-financeiro'),
                404
            );
        }

        return $this->success_response($despesa, 200);
    }

    public function create_despesa(\WP_REST_Request $request)
    {
        $data = $this->sanitize_despesa_data($request->get_json_params());
        $validation = $this->validate_despesa_data($data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $repo = new DespesasRepository();
        $id = $repo->create($data);

        if (false === $id) {
            return $this->error_response(
                'creation_failed',
                __('Erro ao criar despesa.', 'gestor-financeiro'),
                500
            );
        }

        $despesa = $repo->find($id);
        return $this->success_response($despesa, 201);
    }

    public function update_despesa(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new DespesasRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Despesa não encontrada.', 'gestor-financeiro'),
                404
            );
        }

        $data = $this->sanitize_despesa_data($request->get_json_params());
        $validation = $this->validate_despesa_data($data, $id);
        if (is_wp_error($validation)) {
            return $validation;
        }

        if (! $repo->update($id, $data)) {
            return $this->error_response(
                'update_failed',
                __('Erro ao atualizar despesa.', 'gestor-financeiro'),
                500
            );
        }

        $despesa = $repo->find($id);
        return $this->success_response($despesa, 200);
    }

    public function delete_despesa(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new DespesasRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Despesa não encontrada.', 'gestor-financeiro'),
                404
            );
        }

        if (! $repo->delete($id)) {
            return $this->error_response(
                'deletion_failed',
                __('Erro ao eliminar despesa.', 'gestor-financeiro'),
                500
            );
        }

        return $this->success_response(null, 204);
    }

    public function mark_despesa_paid(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new DespesasRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Despesa não encontrada.', 'gestor-financeiro'),
                404
            );
        }

        $params = $request->get_json_params();
        $metodo = isset($params['metodo']) ? sanitize_text_field($params['metodo']) : null;

        if (! $repo->markAsPaid($id, $metodo)) {
            return $this->error_response(
                'update_failed',
                __('Erro ao marcar despesa como paga.', 'gestor-financeiro'),
                500
            );
        }

        $despesa = $repo->find($id);
        return $this->success_response($despesa, 200);
    }

    // Receitas endpoints.
    public function get_receitas(\WP_REST_Request $request): \WP_REST_Response
    {
        $repo = new ReceitasRepository();
        $filters = array();

        if ($request->get_param('estabelecimento_id')) {
            $filters['estabelecimento_id'] = (int) $request->get_param('estabelecimento_id');
        }
        if ($request->get_param('start_date')) {
            $filters['data >='] = sanitize_text_field($request->get_param('start_date'));
        }
        if ($request->get_param('end_date')) {
            $filters['data <='] = sanitize_text_field($request->get_param('end_date'));
        }

        $order_by = array('data' => 'DESC', 'id' => 'DESC');
        $limit = $request->get_param('per_page') ? (int) $request->get_param('per_page') : null;
        $offset = $request->get_param('page') ? ((int) $request->get_param('page') - 1) * ($limit ?: 10) : null;

        $receitas = $repo->findAll($filters, $order_by, $limit, $offset);
        return $this->success_response($receitas, 200);
    }

    public function get_receita(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new ReceitasRepository();
        $receita = $repo->find($id);

        if (! $receita) {
            return $this->error_response(
                'not_found',
                __('Receita não encontrada.', 'gestor-financeiro'),
                404
            );
        }

        return $this->success_response($receita, 200);
    }

    public function create_receita(\WP_REST_Request $request)
    {
        $data = $this->sanitize_receita_data($request->get_json_params());
        $validation = $this->validate_receita_data($data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $repo = new ReceitasRepository();
        $id = $repo->create($data);

        if (false === $id) {
            return $this->error_response(
                'creation_failed',
                __('Erro ao criar receita.', 'gestor-financeiro'),
                500
            );
        }

        $receita = $repo->find($id);
        return $this->success_response($receita, 201);
    }

    public function update_receita(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new ReceitasRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Receita não encontrada.', 'gestor-financeiro'),
                404
            );
        }

        $data = $this->sanitize_receita_data($request->get_json_params());
        $validation = $this->validate_receita_data($data, $id);
        if (is_wp_error($validation)) {
            return $validation;
        }

        if (! $repo->update($id, $data)) {
            return $this->error_response(
                'update_failed',
                __('Erro ao atualizar receita.', 'gestor-financeiro'),
                500
            );
        }

        $receita = $repo->find($id);
        return $this->success_response($receita, 200);
    }

    public function delete_receita(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new ReceitasRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Receita não encontrada.', 'gestor-financeiro'),
                404
            );
        }

        if (! $repo->delete($id)) {
            return $this->error_response(
                'deletion_failed',
                __('Erro ao eliminar receita.', 'gestor-financeiro'),
                500
            );
        }

        return $this->success_response(null, 204);
    }

    // Obrigacoes endpoints.
    public function get_obrigacoes(\WP_REST_Request $request): \WP_REST_Response
    {
        $repo = new ObrigacoesRepository();
        $obrigacoes = $repo->findAll(array(), array('nome' => 'ASC'));
        return $this->success_response($obrigacoes, 200);
    }

    public function get_obrigacao(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new ObrigacoesRepository();
        $obrigacao = $repo->find($id);

        if (! $obrigacao) {
            return $this->error_response(
                'not_found',
                __('Obrigação não encontrada.', 'gestor-financeiro'),
                404
            );
        }

        return $this->success_response($obrigacao, 200);
    }

    public function create_obrigacao(\WP_REST_Request $request)
    {
        $data = $this->sanitize_obrigacao_data($request->get_json_params());
        $validation = $this->validate_obrigacao_data($data);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $repo = new ObrigacoesRepository();
        $id = $repo->create($data);

        if (false === $id) {
            return $this->error_response(
                'creation_failed',
                __('Erro ao criar obrigação.', 'gestor-financeiro'),
                500
            );
        }

        $obrigacao = $repo->find($id);
        return $this->success_response($obrigacao, 201);
    }

    public function update_obrigacao(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new ObrigacoesRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Obrigação não encontrada.', 'gestor-financeiro'),
                404
            );
        }

        $data = $this->sanitize_obrigacao_data($request->get_json_params());
        $validation = $this->validate_obrigacao_data($data, $id);
        if (is_wp_error($validation)) {
            return $validation;
        }

        if (! $repo->update($id, $data)) {
            return $this->error_response(
                'update_failed',
                __('Erro ao atualizar obrigação.', 'gestor-financeiro'),
                500
            );
        }

        $obrigacao = $repo->find($id);
        return $this->success_response($obrigacao, 200);
    }

    public function delete_obrigacao(\WP_REST_Request $request)
    {
        $id = (int) $request->get_param('id');
        $repo = new ObrigacoesRepository();

        if (! $repo->find($id)) {
            return $this->error_response(
                'not_found',
                __('Obrigação não encontrada.', 'gestor-financeiro'),
                404
            );
        }

        if (! $repo->delete($id)) {
            return $this->error_response(
                'deletion_failed',
                __('Erro ao eliminar obrigação.', 'gestor-financeiro'),
                500
            );
        }

        return $this->success_response(null, 204);
    }

    // Settings endpoints.
    public function get_settings(\WP_REST_Request $request): \WP_REST_Response
    {
        $repo = new SettingsRepository();
        $settings = $repo->getAll();
        return $this->success_response($settings, 200);
    }

    public function update_settings(\WP_REST_Request $request)
    {
        $data = $request->get_json_params();
        if (! is_array($data)) {
            return $this->error_response(
                'invalid_data',
                __('Dados inválidos.', 'gestor-financeiro'),
                400
            );
        }

        $repo = new SettingsRepository();
        foreach ($data as $key => $value) {
            $repo->set(sanitize_text_field($key), $value);
        }

        $settings = $repo->getAll();
        return $this->success_response($settings, 200);
    }

    // Dashboard summary endpoint.
    public function get_dashboard_summary(\WP_REST_Request $request): \WP_REST_Response
    {
        $month = (int) $request->get_param('month') ?: (int) current_time('n');
        $year = (int) $request->get_param('year') ?: (int) current_time('Y');
        $estabelecimento_id = $request->get_param('estabelecimento_id') ? (int) $request->get_param('estabelecimento_id') : null;

        $receitas_repo = new ReceitasRepository();
        $despesas_repo = new DespesasRepository();

        $receita_total = $receitas_repo->getMonthlyTotal($month, $year, $estabelecimento_id);
        $despesa_total = $despesas_repo->getMonthlyTotal($month, $year, $estabelecimento_id);
        $resultado = $receita_total - $despesa_total;
        $por_pagar = 0; // Will be calculated from pending expenses.

        $pending_expenses = $despesas_repo->findPending(null, $estabelecimento_id);
        foreach ($pending_expenses as $expense) {
            $por_pagar += (float) $expense['valor'];
        }

        return $this->success_response(
            array(
                'receita_mes' => $receita_total,
                'despesas_mes' => $despesa_total,
                'resultado' => $resultado,
                'por_pagar' => $por_pagar,
                'month' => $month,
                'year' => $year,
            ),
            200
        );
    }

    // Calendar events endpoint.
    public function get_calendar_events(\WP_REST_Request $request): \WP_REST_Response
    {
        $start_date = $request->get_param('start') ?: current_time('Y-m-d');
        $end_date = $request->get_param('end') ?: date('Y-m-d', strtotime('+30 days'));

        $despesas_repo = new DespesasRepository();
        $funcionarios_repo = new FuncionariosRepository();
        $obrigacoes_repo = new ObrigacoesRepository();
        $estabelecimentos_repo = new EstabelecimentosRepository();

        $events = array();

        // Get pending expenses.
        $pending_expenses = $despesas_repo->findPending($end_date);
        foreach ($pending_expenses as $expense) {
            if ($expense['vencimento'] >= $start_date && $expense['vencimento'] <= $end_date) {
                $events[] = array(
                    'id' => 'despesa_' . $expense['id'],
                    'title' => $expense['descricao'],
                    'date' => $expense['vencimento'],
                    'type' => 'despesa',
                    'value' => (float) $expense['valor'],
                    'data' => $expense,
                );
            }
        }

        // Get recurring salaries.
        $funcionarios = $funcionarios_repo->findAll();
        $current_day = (int) current_time('j');
        foreach ($funcionarios as $funcionario) {
            $estabelecimento = $estabelecimentos_repo->find((int) $funcionario['estabelecimento_id']);
            if ($estabelecimento && $estabelecimento['dia_renda']) {
                $day = (int) $estabelecimento['dia_renda'];
                $month = (int) current_time('n');
                $year = (int) current_time('Y');
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);

                if ($date >= $start_date && $date <= $end_date) {
                    $events[] = array(
                        'id' => 'salario_' . $funcionario['id'],
                        'title' => sprintf('Salário: %s', $funcionario['nome']),
                        'date' => $date,
                        'type' => 'salario',
                        'value' => (float) $funcionario['valor_base'],
                        'data' => $funcionario,
                    );
                }
            }
        }

        // Get obligations.
        $obrigacoes = $obrigacoes_repo->findAll();
        foreach ($obrigacoes as $obrigacao) {
            // Calculate next occurrence based on periodicity.
            // This is simplified - full implementation would calculate actual dates.
            if ('mensal' === $obrigacao['periodicidade']) {
                $day = $obrigacao['dia_fim'] ?: $obrigacao['dia_inicio'] ?: 1;
                $month = (int) current_time('n');
                $year = (int) current_time('Y');
                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);

                if ($date >= $start_date && $date <= $end_date) {
                    $events[] = array(
                        'id' => 'obrigacao_' . $obrigacao['id'],
                        'title' => $obrigacao['nome'],
                        'date' => $date,
                        'type' => 'obrigacao',
                        'data' => $obrigacao,
                    );
                }
            }
        }

        return $this->success_response($events, 200);
    }

    // Salaries endpoints.
    public function get_salaries(\WP_REST_Request $request): \WP_REST_Response
    {
        $repo = new FuncionariosRepository();
        $filters = array();
        if ($request->get_param('estabelecimento_id')) {
            $filters['estabelecimento_id'] = (int) $request->get_param('estabelecimento_id');
        }
        if ($request->get_param('tipo_pagamento')) {
            $filters['tipo_pagamento'] = sanitize_text_field($request->get_param('tipo_pagamento'));
        }

        $funcionarios = $repo->findAll($filters, array('nome' => 'ASC'));
        return $this->success_response($funcionarios, 200);
    }

    public function mark_salary_paid(\WP_REST_Request $request)
    {
        // For salaries, we create a despesa record.
        $funcionario_id = (int) $request->get_param('id');
        $funcionarios_repo = new FuncionariosRepository();
        $funcionario = $funcionarios_repo->find($funcionario_id);

        if (! $funcionario) {
            return $this->error_response(
                'not_found',
                __('Funcionário não encontrado.', 'gestor-financeiro'),
                404
            );
        }

        $params = $request->get_json_params();
        $data = array(
            'data' => $params['data'] ?? current_time('Y-m-d'),
            'estabelecimento_id' => $funcionario['estabelecimento_id'],
            'tipo' => 'salario',
            'funcionario_id' => $funcionario_id,
            'descricao' => sprintf('Salário: %s', $funcionario['nome']),
            'valor' => $funcionario['valor_base'],
            'pago' => 1,
            'pago_em' => current_time('mysql'),
        );

        $despesas_repo = new DespesasRepository();
        $id = $despesas_repo->create($data);

        if (false === $id) {
            return $this->error_response(
                'creation_failed',
                __('Erro ao marcar salário como pago.', 'gestor-financeiro'),
                500
            );
        }

        $despesa = $despesas_repo->find($id);
        return $this->success_response($despesa, 200);
    }

    // Reports endpoints.
    public function get_monthly_report(\WP_REST_Request $request): \WP_REST_Response
    {
        $month = (int) $request->get_param('month') ?: (int) current_time('n');
        $year = (int) $request->get_param('year') ?: (int) current_time('Y');
        $estabelecimento_id = $request->get_param('estabelecimento_id') ? (int) $request->get_param('estabelecimento_id') : null;

        // Validate month and year.
        if ($month < 1 || $month > 12) {
            return $this->error_response(
                'invalid_month',
                __('Mês inválido. Deve estar entre 1 e 12.', 'gestor-financeiro'),
                400
            );
        }

        if ($year < 2000 || $year > 2100) {
            return $this->error_response(
                'invalid_year',
                __('Ano inválido.', 'gestor-financeiro'),
                400
            );
        }

        $reports = new Reports();
        $report = $reports->gerarMensal($month, $year, $estabelecimento_id);

        return $this->success_response($report, 200);
    }

    public function export_monthly_report(\WP_REST_Request $request)
    {
        $month = (int) $request->get_param('month') ?: (int) current_time('n');
        $year = (int) $request->get_param('year') ?: (int) current_time('Y');
        $estabelecimento_id = $request->get_param('estabelecimento_id') ? (int) $request->get_param('estabelecimento_id') : null;

        // Validate month and year.
        if ($month < 1 || $month > 12) {
            return $this->error_response(
                'invalid_month',
                __('Mês inválido. Deve estar entre 1 e 12.', 'gestor-financeiro'),
                400
            );
        }

        if ($year < 2000 || $year > 2100) {
            return $this->error_response(
                'invalid_year',
                __('Ano inválido.', 'gestor-financeiro'),
                400
            );
        }

        $reports = new Reports();
        $report = $reports->gerarMensal($month, $year, $estabelecimento_id);
        $csv_content = $reports->exportToCSV($report);

        // Return CSV as download via REST response with custom headers.
        $response = new \WP_REST_Response($csv_content, 200);
        $response->header('Content-Type', 'text/csv; charset=UTF-8');
        $response->header('Content-Disposition', 'attachment; filename="relatorio-' . $month . '-' . $year . '.csv"');
        $response->header('Content-Length', (string) strlen($csv_content));

        return $response;
    }

    // CSV endpoints.
    public function export_csv(\WP_REST_Request $request): \WP_REST_Response
    {
        $type = sanitize_text_field($request->get_param('type') ?: 'despesas');
        $start_date = sanitize_text_field($request->get_param('start_date') ?: '');
        $end_date = sanitize_text_field($request->get_param('end_date') ?: '');
        $estabelecimento_id = $request->get_param('estabelecimento_id') ? (int) $request->get_param('estabelecimento_id') : null;

        if (!in_array($type, array('despesas', 'receitas'), true)) {
            return $this->error_response(
                'invalid_type',
                __('Tipo inválido. Use "despesas" ou "receitas".', 'gestor-financeiro'),
                400
            );
        }

        $csv_handler = new CSV();
        $csv_content = $csv_handler->export($type, $start_date ?: null, $end_date ?: null, $estabelecimento_id);

        // Return CSV as download via REST response with custom headers.
        $response = new \WP_REST_Response($csv_content, 200);
        $response->header('Content-Type', 'text/csv; charset=UTF-8');
        $filename = sprintf('export-%s-%s.csv', $type, current_time('Y-m-d'));
        $response->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->header('Content-Length', (string) strlen($csv_content));

        return $response;
    }

    public function import_csv(\WP_REST_Request $request): \WP_REST_Response
    {
        $type = sanitize_text_field($request->get_param('type') ?: 'despesas');
        $csv_content = $request->get_param('csv_content');

        if (!in_array($type, array('despesas', 'receitas'), true)) {
            return $this->error_response(
                'invalid_type',
                __('Tipo inválido. Use "despesas" ou "receitas".', 'gestor-financeiro'),
                400
            );
        }

        if (empty($csv_content)) {
            return $this->error_response(
                'empty_content',
                __('Conteúdo CSV vazio.', 'gestor-financeiro'),
                400
            );
        }

        $csv_handler = new CSV();
        $result = $csv_handler->import($csv_content, $type);

        return $this->success_response($result, 200);
    }

    public function execute_csv_import(\WP_REST_Request $request): \WP_REST_Response
    {
        $data = $request->get_json_params();
        if (!is_array($data)) {
            return $this->error_response(
                'invalid_data',
                __('Dados inválidos.', 'gestor-financeiro'),
                400
            );
        }

        $preview_data = $data['preview_data'] ?? array();
        $type = sanitize_text_field($data['type'] ?? 'despesas');

        if (!in_array($type, array('despesas', 'receitas'), true)) {
            return $this->error_response(
                'invalid_type',
                __('Tipo inválido.', 'gestor-financeiro'),
                400
            );
        }

        if (empty($preview_data)) {
            return $this->error_response(
                'empty_data',
                __('Dados de preview vazios.', 'gestor-financeiro'),
                400
            );
        }

        $csv_handler = new CSV();
        $result = $csv_handler->execute_import($preview_data, $type);

        return $this->success_response($result, 200);
    }

    // Apartment endpoints.
    public function get_apartment_categories(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->success_response(
            array(
                'expense_categories' => Apartment::get_expense_categories(),
                'revenue_categories' => Apartment::get_revenue_categories(),
            ),
            200
        );
    }

    public function generate_apartment_recurring(\WP_REST_Request $request): \WP_REST_Response
    {
        $estabelecimento_id = (int) $request->get_param('id');
        $data = $request->get_json_params();

        if (!is_array($data)) {
            return $this->error_response(
                'invalid_data',
                __('Dados inválidos.', 'gestor-financeiro'),
                400
            );
        }

        $start_date = sanitize_text_field($data['start_date'] ?? '');
        $end_date = sanitize_text_field($data['end_date'] ?? '');

        if (empty($start_date) || empty($end_date)) {
            return $this->error_response(
                'missing_dates',
                __('Data de início e data de fim são obrigatórias.', 'gestor-financeiro'),
                400
            );
        }

        // Validate dates.
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);

        if (false === $start_timestamp || false === $end_timestamp) {
            return $this->error_response(
                'invalid_dates',
                __('Datas inválidas.', 'gestor-financeiro'),
                400
            );
        }

        if ($start_timestamp > $end_timestamp) {
            return $this->error_response(
                'invalid_date_range',
                __('Data de início deve ser anterior à data de fim.', 'gestor-financeiro'),
                400
            );
        }

        $apartment = new Apartment();
        $result = $apartment->generate_recurring_transactions($estabelecimento_id, $start_date, $end_date);

        if (!$result['success']) {
            return $this->error_response(
                'generation_failed',
                $result['error'] ?? __('Erro ao gerar transações recorrentes.', 'gestor-financeiro'),
                500
            );
        }

        return $this->success_response($result, 200);
    }

    /**
     * Register admin utility routes.
     *
     * @return void
     */
    private function register_admin_routes(): void
    {
        register_rest_route(
            self::NAMESPACE,
            '/admin/clear-all-data',
            array(
                'methods'             => 'POST',
                'callback'            => array($this, 'clear_all_data'),
                'permission_callback' => array($this, 'check_edit_permission'),
            )
        );
    }

    // Admin endpoints.
    public function clear_all_data(\WP_REST_Request $request): \WP_REST_Response
    {
        // Verify user has edit permission
        if (!Permissions::can_edit()) {
            return $this->error_response(
                'insufficient_permissions',
                __('Não tem permissão para realizar esta ação.', 'gestor-financeiro'),
                403
            );
        }

        global $wpdb;

        try {
            // Get all table names
            $tables = new \GestorFinanceiro\DB\Tables();
            
            // Tables to clear (in order to respect foreign keys)
            // Note: settings table is NOT cleared, only other data tables
            $table_names = array(
                'logs',              // No dependencies
                'alertas',           // No dependencies
                'despesas',          // Depends on estabelecimentos, fornecedores
                'receitas',          // Depends on estabelecimentos
                'recorrencias',      // Depends on various
                'funcionarios',      // Depends on estabelecimentos
                'obrigacoes',        // No dependencies
                'fornecedores',      // No dependencies
                'estabelecimentos',  // Base table
                // 'settings' is NOT cleared - we want to keep settings
            );

            // Disable foreign key checks temporarily
            $wpdb->query('SET FOREIGN_KEY_CHECKS = 0');

            // Clear each table
            foreach ($table_names as $table_key) {
                $table_name = $tables->get_table_name($table_key);
                $wpdb->query("TRUNCATE TABLE {$table_name}");
            }

            // Re-enable foreign key checks
            $wpdb->query('SET FOREIGN_KEY_CHECKS = 1');

            // Reset the seeded flag so demo data can be recreated
            delete_option('gestor_financeiro_seeded');

            return $this->success_response(
                array(
                    'message' => __('Todos os dados foram apagados com sucesso.', 'gestor-financeiro'),
                ),
                200
            );
        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            $wpdb->query('SET FOREIGN_KEY_CHECKS = 1');

            return $this->error_response(
                'clear_error',
                __('Erro ao apagar dados: ', 'gestor-financeiro') . $e->getMessage(),
                500
            );
        }
    }

    // Help endpoints.
    public function get_help_guide(\WP_REST_Request $request): \WP_REST_Response
    {
        $guide_file = GF_PLUGIN_DIR . 'docs/GUIA-DE-UTILIZADOR.md';
        
        if (!file_exists($guide_file)) {
            return $this->error_response(
                'guide_not_found',
                __('Guia de utilizador não encontrado.', 'gestor-financeiro'),
                404
            );
        }

        $content = file_get_contents($guide_file);
        
        if ($content === false) {
            return $this->error_response(
                'guide_read_error',
                __('Erro ao ler o guia de utilizador.', 'gestor-financeiro'),
                500
            );
        }

        // Convert Markdown to HTML (simple conversion)
        $converted = $this->markdown_to_html($content);

        return $this->success_response(
            array(
                'html' => $converted['html'],
                'toc' => $converted['toc'],
                'raw' => $content,
            ),
            200
        );
    }

    /**
     * Simple Markdown to HTML converter.
     *
     * @param string $markdown Markdown content.
     * @return array<string, mixed> Array with 'html' content and 'toc' (table of contents).
     */
    private function markdown_to_html(string $markdown): array
    {
        $html = $markdown;
        $toc = array();

        // Extract table of contents section first
        if (preg_match('/## Índice\s*\n\n(.*?)(?=\n---|\n## |$)/s', $html, $toc_match)) {
            $toc_content = $toc_match[1];
            // Remove TOC from main content
            $html = preg_replace('/## Índice\s*\n\n.*?(?=\n---|\n## |$)/s', '', $html, 1);
            // Also remove the separator line if present
            $html = preg_replace('/^---\s*$/m', '', $html, 1);
            
            // Parse TOC links - handle both numbered and unnumbered lists
            preg_match_all('/^\d+\.\s+\[(.*?)\]\(#([^\)]+)\)/m', $toc_content, $toc_matches, PREG_SET_ORDER);
            foreach ($toc_matches as $match) {
                $anchor_raw = trim($match[2]);
                // Sanitize anchor to match what sanitize_title will produce
                $anchor_sanitized = sanitize_title($anchor_raw);
                $toc[] = array(
                    'text' => trim($match[1]),
                    'anchor' => $anchor_sanitized, // Store sanitized version
                );
            }
        }

        // Headers with IDs - also wrap sections for show/hide functionality
        $html = preg_replace_callback('/^### (.*)$/m', function($matches) {
            $text = trim($matches[1]);
            $id = sanitize_title($text);
            return '<h3 id="' . $id . '">' . $text . '</h3>';
        }, $html);
        
        $html = preg_replace_callback('/^## (.*)$/m', function($matches) {
            $text = trim($matches[1]);
            $id = sanitize_title($text);
            return '<h2 id="' . $id . '">' . $text . '</h2>';
        }, $html);
        
        $html = preg_replace_callback('/^# (.*)$/m', function($matches) {
            $text = trim($matches[1]);
            $id = sanitize_title($text);
            return '<h1 id="' . $id . '">' . $text . '</h1>';
        }, $html);

        // Convert anchor links to internal anchors
        $html = preg_replace('/\[([^\]]+)\]\(#([^\)]+)\)/', '<a href="#$2">$1</a>', $html);

        // Bold
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);

        // External links
        $html = preg_replace('/\[([^\]]+)\]\((http[^\)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $html);

        // Code blocks
        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);

        // Lists
        $html = preg_replace('/^\* (.*)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/^- (.*)$/m', '<li>$1</li>', $html);
        
        // Wrap consecutive list items in ul
        $html = preg_replace('/(<li>.*<\/li>\n?)+/s', '<ul>$0</ul>', $html);

        // Paragraphs (lines not starting with #, *, -, etc.)
        $lines = explode("\n", $html);
        $result = array();
        $in_paragraph = false;
        $paragraph = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            if (empty($trimmed)) {
                if ($in_paragraph && !empty($paragraph)) {
                    $result[] = '<p>' . trim($paragraph) . '</p>';
                    $paragraph = '';
                    $in_paragraph = false;
                }
                $result[] = '';
                continue;
            }

            // Check if line is a header, list, or code block
            if (preg_match('/^<(h[1-6]|ul|li|code|strong|a)/', $trimmed) || 
                preg_match('/^#+ |^\* |^- |^`/', $trimmed)) {
                if ($in_paragraph && !empty($paragraph)) {
                    $result[] = '<p>' . trim($paragraph) . '</p>';
                    $paragraph = '';
                    $in_paragraph = false;
                }
                $result[] = $line;
            } else {
                if (!$in_paragraph) {
                    $in_paragraph = true;
                    $paragraph = $line;
                } else {
                    $paragraph .= ' ' . $line;
                }
            }
        }

        if ($in_paragraph && !empty($paragraph)) {
            $result[] = '<p>' . trim($paragraph) . '</p>';
        }

        $html = implode("\n", $result);

        // Clean up extra whitespace
        $html = preg_replace('/\n{3,}/', "\n\n", $html);

        // Now wrap sections for show/hide functionality
        // Split by H2 headers that are in TOC
        $lines = explode("\n", $html);
        $wrapped_lines = array();
        $current_section = null;
        $first_section_found = false;
        $content_before_first_h2 = array();

        foreach ($lines as $line) {
            // Check if this line is an H2 header that's in the TOC
            if (preg_match('/<h2 id="([^"]+)">(.*?)<\/h2>/', $line, $h2_match)) {
                $h2_id = $h2_match[1];
                
                // Check if this H2 is in TOC
                // The anchor in TOC is already sanitized, and h2_id is also sanitized
                $is_in_toc = false;
                foreach ($toc as $toc_item) {
                    if ($toc_item['anchor'] === $h2_id) {
                        $is_in_toc = true;
                        break;
                    }
                }

                if ($is_in_toc) {
                    // If we have content before first H2, wrap it as first section
                    if (!$first_section_found && !empty($content_before_first_h2)) {
                        // Use the first anchor from TOC (already sanitized)
                        $first_anchor = $toc[0]['anchor'];
                        $wrapped_lines[] = '<div class="gf-help-section" id="section-' . $first_anchor . '" style="display: block;">';
                        $wrapped_lines = array_merge($wrapped_lines, $content_before_first_h2);
                        $wrapped_lines[] = '</div>';
                        $content_before_first_h2 = array();
                    }
                    
                    // Close previous section if exists
                    if ($current_section !== null) {
                        $wrapped_lines[] = '</div>';
                    }
                    
                    // Start new section
                    $display = $first_section_found ? 'none' : 'block';
                    $wrapped_lines[] = '<div class="gf-help-section" id="section-' . $h2_id . '" style="display: ' . $display . ';">';
                    $current_section = $h2_id;
                    $first_section_found = true;
                }
            }
            
            // Add line to appropriate array
            if ($first_section_found) {
                $wrapped_lines[] = $line;
            } else {
                $content_before_first_h2[] = $line;
            }
        }

        // Close last section
        if ($current_section !== null) {
            $wrapped_lines[] = '</div>';
        }

        // If no sections were found, wrap everything in one section
        if (!$first_section_found && !empty($toc)) {
            // Use the first anchor from TOC (already sanitized)
            $first_anchor = $toc[0]['anchor'];
            $html = '<div class="gf-help-section" id="section-' . $first_anchor . '" style="display: block;">' . implode("\n", $content_before_first_h2) . '</div>';
        } else {
            $html = implode("\n", $wrapped_lines);
        }

        return array(
            'html' => $html,
            'toc' => $toc,
        );
    }

    // Sanitization and validation methods.
    private function sanitize_estabelecimento_data(array $data): array
    {
        return array(
            'nome' => isset($data['nome']) ? sanitize_text_field($data['nome']) : '',
            'tipo' => isset($data['tipo']) ? sanitize_text_field($data['tipo']) : 'restaurante',
            'dia_renda' => isset($data['dia_renda']) ? absint($data['dia_renda']) : null,
            'ativo' => isset($data['ativo']) ? absint($data['ativo']) : 1,
        );
    }

    private function validate_estabelecimento_data(array $data, ?int $id = null): bool|\WP_Error
    {
        if (empty($data['nome'])) {
            return $this->error_response(
                'validation_error',
                __('Nome é obrigatório.', 'gestor-financeiro'),
                400
            );
        }

        $valid_types = array('restaurante', 'bar', 'apartamento');
        if (! in_array($data['tipo'], $valid_types, true)) {
            return $this->error_response(
                'validation_error',
                __('Tipo inválido.', 'gestor-financeiro'),
                400
            );
        }

        return true;
    }

    private function sanitize_fornecedor_data(array $data): array
    {
        return array(
            'nome' => isset($data['nome']) ? sanitize_text_field($data['nome']) : '',
            'nif' => isset($data['nif']) ? sanitize_text_field($data['nif']) : null,
            'categoria' => isset($data['categoria']) ? sanitize_text_field($data['categoria']) : null,
            'prazo_pagamento' => isset($data['prazo_pagamento']) ? absint($data['prazo_pagamento']) : null,
            'contacto' => isset($data['contacto']) ? sanitize_text_field($data['contacto']) : null,
            'iban' => isset($data['iban']) ? sanitize_text_field($data['iban']) : null,
            'notas' => isset($data['notas']) ? sanitize_textarea_field($data['notas']) : null,
        );
    }

    private function validate_fornecedor_data(array $data, ?int $id = null): bool|\WP_Error
    {
        if (empty($data['nome'])) {
            return $this->error_response(
                'validation_error',
                __('Nome é obrigatório.', 'gestor-financeiro'),
                400
            );
        }

        return true;
    }

    private function sanitize_funcionario_data(array $data): array
    {
        return array(
            'nome' => isset($data['nome']) ? sanitize_text_field($data['nome']) : '',
            'tipo_pagamento' => isset($data['tipo_pagamento']) ? sanitize_text_field($data['tipo_pagamento']) : 'fixo',
            'valor_base' => isset($data['valor_base']) ? (float) $data['valor_base'] : 0.00,
            'regra_pagamento' => isset($data['regra_pagamento']) ? sanitize_text_field($data['regra_pagamento']) : null,
            'estabelecimento_id' => isset($data['estabelecimento_id']) ? absint($data['estabelecimento_id']) : null,
            'iban' => isset($data['iban']) ? sanitize_text_field($data['iban']) : null,
            'notas' => isset($data['notas']) ? sanitize_textarea_field($data['notas']) : null,
        );
    }

    private function validate_funcionario_data(array $data, ?int $id = null): bool|\WP_Error
    {
        if (empty($data['nome'])) {
            return $this->error_response(
                'validation_error',
                __('Nome é obrigatório.', 'gestor-financeiro'),
                400
            );
        }

        $valid_types = array('fixo', 'diario', 'hora');
        if (! in_array($data['tipo_pagamento'], $valid_types, true)) {
            return $this->error_response(
                'validation_error',
                __('Tipo de pagamento inválido.', 'gestor-financeiro'),
                400
            );
        }

        return true;
    }

    private function sanitize_despesa_data(array $data): array
    {
        return array(
            'data' => isset($data['data']) ? sanitize_text_field($data['data']) : current_time('Y-m-d'),
            'estabelecimento_id' => isset($data['estabelecimento_id']) ? absint($data['estabelecimento_id']) : null,
            'tipo' => isset($data['tipo']) ? sanitize_text_field($data['tipo']) : null,
            'fornecedor_id' => isset($data['fornecedor_id']) ? absint($data['fornecedor_id']) : null,
            'funcionario_id' => isset($data['funcionario_id']) ? absint($data['funcionario_id']) : null,
            'descricao' => isset($data['descricao']) ? sanitize_text_field($data['descricao']) : '',
            'vencimento' => isset($data['vencimento']) ? sanitize_text_field($data['vencimento']) : null,
            'valor' => isset($data['valor']) ? (float) $data['valor'] : 0.00,
            'pago' => isset($data['pago']) ? absint($data['pago']) : 0,
            'metodo' => isset($data['metodo']) ? sanitize_text_field($data['metodo']) : null,
            'anexo' => isset($data['anexo']) ? absint($data['anexo']) : null,
            'notas' => isset($data['notas']) ? sanitize_textarea_field($data['notas']) : null,
        );
    }

    private function validate_despesa_data(array $data, ?int $id = null): bool|\WP_Error
    {
        if (empty($data['descricao'])) {
            return $this->error_response(
                'validation_error',
                __('Descrição é obrigatória.', 'gestor-financeiro'),
                400
            );
        }

        return true;
    }

    private function sanitize_receita_data(array $data): array
    {
        return array(
            'data' => isset($data['data']) ? sanitize_text_field($data['data']) : current_time('Y-m-d'),
            'estabelecimento_id' => isset($data['estabelecimento_id']) ? absint($data['estabelecimento_id']) : null,
            'canal' => isset($data['canal']) ? sanitize_text_field($data['canal']) : null,
            'bruto' => isset($data['bruto']) ? (float) $data['bruto'] : 0.00,
            'taxas' => isset($data['taxas']) ? (float) $data['taxas'] : 0.00,
            'liquido' => isset($data['liquido']) ? (float) $data['liquido'] : 0.00,
            'notas' => isset($data['notas']) ? sanitize_textarea_field($data['notas']) : null,
            'anexo' => isset($data['anexo']) ? absint($data['anexo']) : null,
        );
    }

    private function validate_receita_data(array $data, ?int $id = null): bool|\WP_Error
    {
        // Receitas don't have required fields beyond data.
        return true;
    }

    private function sanitize_obrigacao_data(array $data): array
    {
        return array(
            'nome' => isset($data['nome']) ? sanitize_text_field($data['nome']) : '',
            'periodicidade' => isset($data['periodicidade']) ? sanitize_text_field($data['periodicidade']) : 'mensal',
            'dia_inicio' => isset($data['dia_inicio']) ? absint($data['dia_inicio']) : null,
            'dia_fim' => isset($data['dia_fim']) ? absint($data['dia_fim']) : null,
            'notas' => isset($data['notas']) ? sanitize_textarea_field($data['notas']) : null,
        );
    }

    private function validate_obrigacao_data(array $data, ?int $id = null): bool|\WP_Error
    {
        if (empty($data['nome'])) {
            return $this->error_response(
                'validation_error',
                __('Nome é obrigatório.', 'gestor-financeiro'),
                400
            );
        }

        $valid_periodicities = array('mensal', 'trimestral', 'anual');
        if (! in_array($data['periodicidade'], $valid_periodicities, true)) {
            return $this->error_response(
                'validation_error',
                __('Periodicidade inválida.', 'gestor-financeiro'),
                400
            );
        }

        return true;
    }
}
