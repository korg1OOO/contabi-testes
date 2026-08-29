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
        'input[name="data_inicial"], ' +
        'input[name="data_final"]'
    ).forEach((campo) => {
        mascaraData(campo);
        campo.addEventListener('input', () => mascaraData(campo));
    });

    document.querySelectorAll('input[name="telefone"]').forEach((campo) => {
        mascaraTelefone(campo);
        campo.setAttribute('inputmode', 'tel');
        campo.setAttribute('maxlength', '15');
        campo.setAttribute('placeholder', '(00) 00000-0000');
        campo.addEventListener('input', () => mascaraTelefone(campo));
    });
});

function mascaraTelefone(campo) {
    const numeros = campo.value.replace(/\D/g, '').slice(0, 11);
    let telefone = numeros.replace(/^(\d{2})(\d)/, '($1) $2');

    telefone = numeros.length === 11
        ? telefone.replace(/(\d{5})(\d{1,4})$/, '$1-$2')
        : telefone.replace(/(\d{4})(\d{1,4})$/, '$1-$2');

    campo.value = telefone;
}
