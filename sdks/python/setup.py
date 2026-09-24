from pathlib import Path
from setuptools import setup, find_packages

_readme = Path(__file__).parent / "README.md"

setup(
    name="apiempresas",
    version="1.1.0",
    description="SDK oficial de APIEmpresas.es para Python",
    long_description=_readme.read_text(encoding="utf-8") if _readme.exists() else "",
    long_description_content_type="text/markdown",
    author="APIEmpresas",
    author_email="soporte@apiempresas.es",
    url="https://apiempresas.es",
    packages=find_packages(exclude=["tests", "tests.*"]),
    install_requires=[
        "requests>=2.0.0",
    ],
    classifiers=[
        "Programming Language :: Python :: 3",
        "Operating System :: OS Independent",
    ],
    python_requires=">=3.7",
)
