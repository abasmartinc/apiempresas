from setuptools import setup, find_packages

setup(
    name="apiempresas",
    version="1.0.0",
    description="SDK oficial de APIEmpresas.es para Python",
    author="APIEmpresas",
    author_email="soporte@apiempresas.es",
    url="https://apiempresas.es",
    packages=find_packages(),
    install_requires=[
        "requests>=2.0.0",
    ],
    classifiers=[
        "Programming Language :: Python :: 3",
        "Operating System :: OS Independent",
    ],
    python_requires=">=3.7",
)
