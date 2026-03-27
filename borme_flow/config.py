# -*- coding: utf-8 -*-
import os
import pymysql
from pymysql.cursors import DictCursor
from dotenv import load_dotenv

# Carga el archivo .env que esté en la misma carpeta que este config.py
_env_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), ".env")
load_dotenv(dotenv_path=_env_path)

# Configuración de Base de Datos
DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_USER = os.getenv("DB_USER", "root")
DB_PASS = os.getenv("DB_PASS", "")
DB_NAME = os.getenv("DB_NAME", "apiempresas")
DB_PORT = int(os.getenv("DB_PORT", "3306"))

def mysql_connect():
    """
    Retorna una conexión a la base de datos MySQL con la configuración centralizada.
    """
    return pymysql.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME,
        port=DB_PORT,
        charset="utf8mb4",
        autocommit=False,
        cursorclass=DictCursor
    )

def get_db_params():
    """
    Retorna los parámetros de conexión como un diccionario.
    """
    return {
        "host": DB_HOST,
        "user": DB_USER,
        "password": DB_PASS,
        "database": DB_NAME,
        "port": DB_PORT
    }
