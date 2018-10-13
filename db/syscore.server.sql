--
-- Database Modification for the server
--
-- Configuration database
--


update configuration.config set `value`='/usr/local/www/apache24/data' where name='server_document_root';
update configuration.config set `value`='strata.danielrudy.org' where name='server_hostname';
update configuration.config set `value`=1 where name='server_secure';
update configuration.config set `value`=1 where name='session_regen_enable';
