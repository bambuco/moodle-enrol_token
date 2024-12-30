<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'enrol_token', language 'es'.
 *
 * @package    enrol_token
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['canntenrol'] = 'La inscripción está deshabilitada o inactiva';
$string['canntenrolearly'] = 'No puedes inscribirte aún; la inscripción comienza el {$a}.';
$string['canntenrollate'] = 'Ya no puedes inscribirte, ya que la inscripción finalizó el {$a}.';
$string['cohortnonmemberinfo'] = 'Solo los miembros de la cohorte \'{$a}\' pueden inscribirse mediante token.';
$string['cohortonly'] = 'Solo miembros de la cohorte';
$string['cohortonly_help'] = 'La inscripción mediante token puede estar restringida solo a los miembros de una cohorte especificada. Tenga en cuenta que cambiar esta configuración no afecta a las inscripciones existentes.';
$string['confirmbulkdeleteenrolment'] = '¿Estás seguro de que deseas eliminar estas inscripciones de usuario?';
$string['customwelcomemessage'] = 'Mensaje de bienvenida personalizado';
$string['customwelcomemessage_help'] = 'Se puede agregar un mensaje de bienvenida personalizado como texto simple o formato automático de Moodle, incluidas las etiquetas HTML y las etiquetas multilingües.

Se pueden incluir los siguientes marcadores de posición en el mensaje:

* Nombre del curso {$a->coursename}
* Enlace a la página de perfil del usuario {$a->profileurl}
* Correo electrónico del usuario {$a->email}
* Nombre completo del usuario {$a->fullname}';
$string['defaultrole'] = 'Asignación de rol predeterminada';
$string['defaultrole_desc'] = 'Seleccione el rol que se debe asignar a los usuarios durante la inscripción mediante token';
$string['deleteselectedusers'] = 'Eliminar inscripciones de usuario seleccionadas';
$string['editselectedusers'] = 'Editar inscripciones de usuario seleccionadas';
$string['enrolenddate'] = 'Fecha de finalización';
$string['enrolenddate_help'] = 'Si se habilita, los usuarios pueden inscribirse hasta esta fecha únicamente.';
$string['enrolenddaterror'] = 'La fecha de finalización de la inscripción no puede ser anterior a la fecha de inicio';
$string['enrolid'] = 'ID de inscripción';
$string['enrolme'] = 'Inscríbeme';
$string['enrolperiod'] = 'Duración de la inscripción';
$string['enrolperiod_desc'] = 'Duración predeterminada de la inscripción. Si se establece en cero, la duración de la inscripción será ilimitada de forma predeterminada.';
$string['enrolperiod_help'] = 'Duración de la inscripción, a partir del momento en que el usuario se inscribe. Si está deshabilitado, la duración de la inscripción será ilimitada.';
$string['enrolstartdate'] = 'Fecha de inicio';
$string['enrolstartdate_help'] = 'Si se habilita, los usuarios pueden inscribirse a partir de esta fecha únicamente.';
$string['expiredaction'] = 'Acción de vencimiento de la inscripción';
$string['expiredaction_help'] = 'Seleccione la acción a realizar cuando la inscripción del usuario expire. Tenga en cuenta que algunos datos de usuario y configuraciones se eliminan del curso durante la desinscripción del curso.';
$string['expirymessageenrolledbody'] = 'Estimado {$a->user}:

Esta es una notificación que indica que su inscripción en el curso \'{$a->course}\' vencerá el {$a->timeend}.

Si necesita ayuda, comuníquese con {$a->enroller}.';
$string['expirymessageenrolledsubject'] = 'Notificación de vencimiento de la inscripción mediante token';
$string['expirymessageenrollerbody'] = 'La inscripción en el curso \'{$a->course}\' caducará dentro del próximo {$a->threshold} para los siguientes usuarios:

{$a->users}

Para ampliar su inscripción, acceda a {$a->extendurl}';
$string['expirymessageenrollersubject'] = 'Notificación de vencimiento de la inscripción mediante token';
$string['expirynotifyall'] = 'Profesor y usuario inscrito';
$string['expirynotifyenroller'] = 'Solo profesor';
$string['generate'] = 'Generar';
$string['generatetokens'] = 'Generar tokens';
$string['invalidamount'] = 'Cantidad de tokens a generar no válida. La cantidad debe estar entre 1 y 100';
$string['keyholder'] = 'Deberías haber recibido esta clave de inscripción de:';
$string['longtimenosee'] = 'Desinscribir inactivos después de';
$string['longtimenosee_help'] = 'Después de este período de inactividad, los usuarios se desinscribirán automáticamente del curso. 0 significa que la desinscripción automática está deshabilitada.';
$string['managetokens'] = 'Administrar tokens';
$string['maxenrolled'] = 'Número máximo de usuarios inscritos';
$string['maxenrolled_help'] = 'Especifica el número máximo de usuarios que pueden inscribir tokens. 0 significa que no hay límite.';
$string['maxenrolledreached'] = 'Ya se alcanzó el número máximo de usuarios permitidos para inscribirse mediante token.';
$string['messageprovider:expiry_notification'] = 'Notificaciones de vencimiento de la inscripción mediante token';
$string['newenrols'] = 'Permitir nuevas inscripciones mediante token';
$string['newenrols_desc'] = 'Permitir a los usuarios inscribirse con token en nuevos cursos de forma predeterminada.';
$string['newenrols_help'] = 'Esta configuración determina si un usuario puede inscribirse en este curso.';
$string['pluginname'] = 'Inscripción con token';
$string['pluginname_desc'] = 'El complemento de inscripción con token permite a los usuarios elegir en qué cursos quieren participar e inscribirse usando un token que se les proporciona previamente.';
$string['privacy:metadata'] = 'El complemento de inscripción con token no almacena ningún dato personal.';
$string['role'] = 'Rol asignado por defecto';
$string['sendcoursewelcomemessage'] = 'Enviar mensaje de bienvenida al curso';
$string['sendcoursewelcomemessage_help'] = 'Cuando un token de usuario se inscribe en el curso, se le puede enviar un correo electrónico con un mensaje de bienvenida. Si lo envía el contacto del curso (por defecto, el profesor) y más de un usuario tiene este rol, el correo electrónico lo envía el primer usuario al que se le asigne el rol.';
$string['sendexpirynotificationstask'] = "Tarea de envío de notificaciones de vencimiento de inscripción de token";
$string['status'] = 'Mantener activas las inscripciones de token actuales';
$string['status_desc'] = 'Habilitar el método de inscripción de token en cursos nuevos.';
$string['status_help'] = 'Si se establece en No, los participantes existentes que se inscribieron por sí mismos en el curso ya no tendrán acceso.';
$string['syncenrolmentstask'] = 'Tarea de sincronización de inscripciones de token';
$string['timecreated'] = 'Hora de creación';
$string['timeused'] = 'Tiempo utilizado';
$string['token'] = 'Token de inscripción';
$string['token:config'] = 'Configurar instancias de inscripción de token';
$string['token:enrolself'] = 'Inscribirse automáticamente en el curso';
$string['token:holdkey'] = 'Aparecer como el titular de la clave de inscripción del token';
$string['token:manage'] = 'Administrar usuarios inscritos';
$string['token:unenrol'] = 'Cancelar la inscripción de usuarios del curso';
$string['token:unenrolself'] = 'Cancelar la inscripción del curso';
$string['tokendeleted'] = 'Token eliminado';
$string['tokendeleteerror'] = 'Error al eliminar el token, inténtelo de nuevo';
$string['tokenid'] = 'ID del token';
$string['tokeninvalid'] = 'Token de inscripción incorrecto, inténtalo de nuevo';
$string['tokenlength'] = 'Longitud del token';
$string['tokenlength_help'] = 'Longitud del token a generar';
$string['tokensgenerated'] = 'Se generaron {$a} tokens';
$string['tokenusednotdelete'] = 'Token utilizado, no se puede eliminar';
$string['unenroltokenconfirm'] = '¿Realmente desea darse de baja del curso "{$a}"?';
$string['unenrolusers'] = 'Dar de baja usuarios';
$string['usedbynames'] = 'Usado por';
$string['userid'] = 'ID de usuario';
$string['welcometocourse'] = 'Bienvenido a {$a}';
$string['welcometocoursetext'] = '¡Bienvenido a {$a->coursename}!

Si aún no lo ha hecho, por favor edite su página de perfil para que podamos conocer más sobre usted:

{$a->profileurl}';
