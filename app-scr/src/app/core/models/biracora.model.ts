
export interface encabezado {
    id_bitacora: number;
    tipo_bitacora: number;
    titulo: string;
    folio_reporte: string;
    proyecto: string;
    responsable: string;
    sitio_id: number;
    sitio: string;
    cliente_id: number;
    fecha: string | Date;
    bFinalizado: boolean;
};

export interface tblClima {
    tipo: "clima";
    hora: string;
    temperatura: number;
    temperatura_aparente: number;
    viento: string;
    humedad : string;
    punto_rocio: number;
    presion: string;
    descripcion: string;
};

export interface tblHorarios {
    tipo: "honorario";
    categoria: string;
    nombre: string;
    inicio: string;
    fin: string;
};

export interface tblConsumo {
    tipo: "consumo",
    kit: string;
    color_ral: string;
    wo: string;
    metros_ejec: string;
    equipo: string;
    entregado: string;
};

export interface objActividad {
    tipo: "actividad";
    equipo: string;
    horometro: string;
    no_serie: string;
    modelo: string;
    ot: string;
    descripcion: string;
    fotografias: {
        antes: Array<string>;
        despues: Array<string>
    }
};

export interface objetoGenerico {
    tipo: 'generico';
    [key: string]: any;
}

type BitacoraContenidoItem = tblClima | tblHorarios | objActividad | objetoGenerico;

export interface BitacoraModel {
    encabezado : encabezado;
    contenido: BitacoraContenidoItem[];
};

export interface Clima {
    hora: string,
    temp: string,
    temp_aparente: string,
    viento: string,
    humedad: string,
    punto_rocio: string,
    presion: string,
    desc: string
};