function mascaraCPF(campo) {
    let cpf = campo.value.replace(/\D/g, '').slice(0, 11);

    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
    cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

    campo.value = cpf;
}

function mascaraCNPJ(campo) {
    let cnpj = campo.value.replace(/\D/g, '').slice(0, 14);

    cnpj = cnpj.replace(/^(\d{2})(\d)/, '$1.$2');
    cnpj = cnpj.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    cnpj = cnpj.replace(/\.(\d{3})(\d)/, '.$1/$2');
    cnpj = cnpj.replace(/(\d{4})(\d{1,2})$/, '$1-$2');

    campo.value = cnpj;
}

function mascaraData(campo) {
    let data = campo.value.replace(/\D/g, '').slice(0, 8);

    data = data.replace(/^(\d{2})(\d)/, '$1/$2');
    data = data.replace(/^(\d{2})\/(\d{2})(\d)/, '$1/$2/$3');

    campo.value = data;
}

function configurarCampoDocumento(select, limpar = false) {
    const container = document.getElementById(select.dataset.documentoContainer);
    const campo = document.getElementById(select.dataset.documentoInput);
    const label = container?.querySelector('[data-documento-label]');
    const tipo = select.value;

    if (!container || !campo) {
        return;
    }

    container.hidden = tipo !== 'PF' && tipo !== 'PJ';
    campo.disabled = container.hidden;

    if (container.hidden) {
        campo.value = '';
        return;
    }

    if (limpar) {
        campo.value = '';
    }

    if (tipo === 'PF') {
        if (label) label.textContent = 'CPF';
        campo.placeholder = '000.000.000-00';
        campo.maxLength = 14;
        mascaraCPF(campo);
        return;
    }

    if (label) label.textContent = 'CNPJ';
    campo.placeholder = '00.000.000/0000-00';
    campo.maxLength = 18;
    mascaraCNPJ(campo);
}

document.addEventListener('DOMContentLoaded', () => {
    configurarTiposDePrazo();

    document.querySelectorAll('[data-documento-select]').forEach((select) => {
        const campo = document.getElementById(select.dataset.documentoInput);

        configurarCampoDocumento(select);
        select.addEventListener('change', () => configurarCampoDocumento(select, true));
        campo?.addEventListener('input', () => {
            select.value === 'PF' ? mascaraCPF(campo) : mascaraCNPJ(campo);
        });
    });

    document.querySelectorAll(
        'input[name="data_deposito"], ' +
        'input[name="data_concessao"], ' +
        'input[name="data_vencimento"], ' +
        'input[name="data_proxima_anuidade"], ' +
        'input[name="data_renovacao"], ' +
        'input[name="data_oposicao"], ' +
        'input[name="data_prorrogacao"], ' +
        'input[name="data_manifestacao"], ' +
        'input[name="data_inicial"], ' +
        'input[name="data_final"]'
    ).forEach((campo) => {
        mascaraData(campo);
        campo.addEventListener('input', () => {
            mascaraData(campo);
            validarDatasFormulario(campo.form);
        });
    });

    document.querySelectorAll('form').forEach((form) => {
        if (!form.querySelector('input[name="data_deposito"]')) return;
        validarDatasFormulario(form);
        form.addEventListener('submit', () => validarDatasFormulario(form));
    });

    document.querySelectorAll('input[name="telefone"]').forEach((campo) => {
        mascaraTelefone(campo);
        campo.setAttribute('inputmode', 'tel');
        campo.setAttribute('maxlength', '15');
        campo.setAttribute('placeholder', '(00) 00000-0000');
        campo.addEventListener('input', () => mascaraTelefone(campo));
    });
});

function configurarTiposDePrazo() {
    const ativoSelect = document.getElementById('prazo-ativo');
    const tipoSelect = document.getElementById('prazo-tipo');

    if (!ativoSelect || !tipoSelect) return;

    const placeholder = tipoSelect.querySelector('option[value=""]');
    const opcoesTipo = Array.from(tipoSelect.options).filter(
        (option) => option.value !== ''
    );
    const feedback = tipoSelect.parentElement.querySelector(
        '[data-tipo-prazo-feedback]'
    );

    opcoesTipo.forEach((option) => {
        option.dataset.rotuloOriginal = option.textContent;
    });

    const atualizarTipos = (mudouAtivo = false) => {
        const ativoOption = ativoSelect.selectedOptions[0];
        const tipoAtivo = ativoOption?.dataset.tipoAtivo;
        const tipoAtual = tipoSelect.value;

        if (placeholder) {
            placeholder.hidden = Boolean(tipoAtivo);
            placeholder.disabled = Boolean(tipoAtivo);
        }

        if (!tipoAtivo) {
            tipoSelect.value = '';
            tipoSelect.disabled = true;
            tipoSelect.setCustomValidity('Selecione um processo primeiro.');
            if (feedback) feedback.hidden = true;
            return;
        }

        tipoSelect.disabled = false;
        const opcoesPermitidas = [];
        let tipoAtualPermitido = false;

        opcoesTipo.forEach((option) => {
            const tiposAtivo = (option.dataset.tiposAtivo || '').split(/\s+/);
            const permitido = tiposAtivo.includes(tipoAtivo);
            const manterLegadoSelecionado = !permitido
                && !mudouAtivo
                && option.value === tipoAtual;

            option.hidden = !permitido && !manterLegadoSelecionado;
            option.disabled = !permitido && !manterLegadoSelecionado;
            option.textContent = permitido
                ? option.dataset.rotuloOriginal
                : `${option.dataset.rotuloOriginal} — incompatível com patente; selecione outro`;

            if (permitido) opcoesPermitidas.push(option);
            if (option.value === tipoAtual && permitido) tipoAtualPermitido = true;
        });

        if (tipoAtualPermitido) {
            tipoSelect.value = tipoAtual;
        } else if (mudouAtivo || !tipoAtual) {
            tipoSelect.value = opcoesPermitidas[0]?.value || '';
        }

        const invalido = !opcoesTipo.some(
            (option) => option.value === tipoSelect.value
                && (option.dataset.tiposAtivo || '').split(/\s+/).includes(tipoAtivo)
        );
        const mensagem = 'Esse tipo de prazo não se aplica ao ativo selecionado. Escolha um tipo compatível.';
        tipoSelect.setCustomValidity(invalido ? mensagem : '');
        if (feedback) feedback.hidden = !invalido;
    };

    atualizarTipos();
    ativoSelect.addEventListener('change', () => atualizarTipos(true));
    tipoSelect.addEventListener('change', () => atualizarTipos());
}

function mascaraTelefone(campo) {
    const numeros = campo.value.replace(/\D/g, '').slice(0, 11);
    let telefone = numeros.replace(/^(\d{2})(\d)/, '($1) $2');

    telefone = numeros.length === 11
        ? telefone.replace(/(\d{5})(\d{1,4})$/, '$1-$2')
        : telefone.replace(/(\d{4})(\d{1,4})$/, '$1-$2');

    campo.value = telefone;
}

function dataCivilBR(valor) {
    const formato = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(valor);
    if (!formato) return null;
    const dia = Number(formato[1]), mes = Number(formato[2]), ano = Number(formato[3]);
    if (ano < 1000 || ano > 9999 || mes < 1 || mes > 12) return null;
    const ultimoDia = new Date(Date.UTC(ano, mes, 0)).getUTCDate();
    if (dia < 1 || dia > ultimoDia) return null;
    return `${ano.toString().padStart(4, '0')}-${formato[2]}-${formato[1]}`;
}

function validarDatasFormulario(form) {
    if (!form) return;
    const nomes = [
        'data_deposito',
        'data_concessao',
        'data_vencimento',
        'data_proxima_anuidade',
        'data_renovacao',
        'data_oposicao',
        'data_prorrogacao',
        'data_manifestacao'
    ];
    const campos = Object.fromEntries(nomes.map(n => [n, form.querySelector(`[name="${n}"]`)]));
    const datas = {};
    for (const nome of nomes) {
        const campo = campos[nome];
        if (!campo) continue;
        campo.setCustomValidity('');
        if (campo.value.trim() === '') continue;
        datas[nome] = dataCivilBR(campo.value.trim());
        if (datas[nome] === null && /^\d{2}\/\d{2}\/\d{4}$/.test(campo.value.trim())) {
            campo.setCustomValidity('Data inválida. Verifique dia, mês e ano.');
        }
    }
    if (datas.data_deposito && datas.data_vencimento && datas.data_vencimento < datas.data_deposito) {
        campos.data_vencimento.setCustomValidity('O vencimento não pode ser anterior ao depósito.');
    }
    if (datas.data_deposito && datas.data_concessao && datas.data_concessao < datas.data_deposito) {
        campos.data_concessao.setCustomValidity('A concessão não pode ser anterior ao depósito.');
    }
}
