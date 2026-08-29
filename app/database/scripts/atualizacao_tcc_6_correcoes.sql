-- Contabi - atualização dos 6 ajustes de revisão do TCC
-- Execute apenas em uma base existente. O script.sql já foi atualizado para instalações novas.

USE db_contabi;

-- Permite o mesmo número de processo em carteiras de usuários diferentes.
-- A aplicação continua bloqueando duplicidade dentro da mesma carteira.
ALTER TABLE marcas DROP INDEX numero_processo;
ALTER TABLE patentes DROP INDEX numero_processo;
